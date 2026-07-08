  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('productManager', () => ({
        editModalOpen: false,
        editingProduct: null,
        
        formData: {
          name: '',
          description: '',
          category_id: '',
          price: '',
          cost_price: '',
          weight_grams: '',
          stock: '',
          bundles: [],
          bundle_only: false,
          color_options: '',
          size_options: '',
        },

        openEditModal(product) {
          this.editingProduct = product;
          this.formData.name = product.name;
          this.formData.description = product.description;
          this.formData.category_id = product.category_id;
          this.formData.price = product.price;
          this.formData.cost_price = product.cost_price;
          this.formData.weight_grams = product.weight_grams;
          this.formData.stock = product.stock;
          this.formData.bundles = product.bundles || [];
          this.formData.bundle_only = product.bundle_only || false;
          this.formData.color_options = (product.color_options || []).join(', ');
          this.formData.size_options = (product.size_options || []).join(', ');
          
          this.editModalOpen = true;
        },

        closeEditModal() {
          this.editModalOpen = false;
          setTimeout(() => { this.editingProduct = null; }, 300);
        },

        generatingThumbnails: false,
        generatingThumbnailMode: null,
        generatedThumbnails: [],
        thumbnailModalOpen: false,

        generateAIThumbnails(mode) {
          let fileInput = mode === 'add' ? document.getElementById('add-image') : document.getElementById('edit-image');
          if (!fileInput.files || fileInput.files.length === 0) {
            alert('Please select a raw image file first to generate AI variations.');
            return;
          }
          
          let formData = new FormData();
          formData.append('_token', '{{ csrf_token() }}');
          formData.append('image', fileInput.files[0]);
          
          this.generatingThumbnails = true;
          this.generatingThumbnailMode = mode;
          
          fetch('{{ route("products.ai-generate-thumbnails") }}', {
            method: 'POST',
            body: formData
          })
          .then(res => res.json())
          .then(data => {
            this.generatingThumbnails = false;
            
            if (data.error) {
              this.generatingThumbnailMode = null;
              alert(data.error);
              return;
            }
            
            this.generatedThumbnails = data.urls;
            this.thumbnailModalOpen = true;
          })
          .catch(err => {
            this.generatingThumbnails = false;
            this.generatingThumbnailMode = null;
            alert('An error occurred communicating with the AI server.');
            console.error(err);
          });
        },

        selectThumbnail(url, mode) {
          if (mode === 'add') {
            document.getElementById('ai_image_url_add').value = url;
            document.getElementById('add_thumbnail_preview').src = url;
            document.getElementById('add_thumbnail_preview_container').style.display = 'block';
          } else {
            document.getElementById('ai_image_url_edit').value = url;
            document.getElementById('edit_thumbnail_preview').src = url;
            document.getElementById('edit_thumbnail_preview_container').style.display = 'block';
          }
          this.thumbnailModalOpen = false;
          this.generatingThumbnailMode = null;
        },

        generatingAI: false,
        generatingMode: null,
        
        generateDetails(mode) {
          let form, descInput;
          
          if (mode === 'add') {
            form = document.getElementById('add-product-form');
            descInput = document.getElementById('add-desc');
          } else {
            form = document.getElementById('edit-product-form');
          }

          let formData = new FormData(form);

          let nameVal = formData.get('name');
          if (!nameVal || nameVal.trim() === '') {
            alert('Please enter a Product Name and other details first before generating.');
            return;
          }
          
          // Remove _method field if present (from the edit form's @method('PUT')),
          // otherwise Laravel will treat this fetch request as a PUT and return 405.
          formData.delete('_method');
          
          formData.append('_token', '{{ csrf_token() }}');

          this.generatingAI = true;
          this.generatingMode = mode;

          fetch('{{ route("products.ai-generate") }}', {
            method: 'POST',
            body: formData
          })
          .then(res => res.json())
          .then(data => {
            this.generatingAI = false;
            this.generatingMode = null;
            
            if (data.error) {
              alert(data.error);
              return;
            }
            
            if (mode === 'add') {
              descInput.value = data.description;
            } else {
              this.formData.description = data.description;
            }
          })
          .catch(err => {
            this.generatingAI = false;
            this.generatingMode = null;
            alert('An error occurred communicating with the server.');
            console.error(err);
          });
        }
      }));
    });
  </script>

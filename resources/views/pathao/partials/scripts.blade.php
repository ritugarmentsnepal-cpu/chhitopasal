  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('locationFinder', () => ({
        cities: [],
        citySearch: '',
        loadingCities: true,
        
        showModal: false,
        selectedCity: null,
        zones: [],
        zoneSearch: '',
        loadingZones: false,
        expandedZoneId: null,

        async init() {
          try {
            const res = await fetch('{{ url("api/pathao/cities") }}');
            this.cities = await res.json();
          } catch (e) {
            console.error('Failed to load cities');
          } finally {
            this.loadingCities = false;
          }
        },

        get filteredCities() {
          if (!this.citySearch) return this.cities;
          const s = this.citySearch.toLowerCase();
          return this.cities.filter(c => c.city_name.toLowerCase().includes(s));
        },

        get filteredZones() {
          if (!this.zoneSearch) return this.zones;
          const s = this.zoneSearch.toLowerCase();
          
          return this.zones.filter(z => {
            if (z.zone_name.toLowerCase().includes(s)) return true;
            if (z.areasLoaded && z.areas) {
              return z.areas.some(a => a.area_name.toLowerCase().includes(s));
            }
            return false;
          });
        },

        async openCityModal(city) {
          this.selectedCity = city;
          this.showModal = true;
          this.zoneSearch = '';
          this.expandedZoneId = null;
          this.zones = [];
          this.loadingZones = true;

          try {
            const res = await fetch('{{ url("api/pathao/zones") }}/' + city.city_id);
            const data = await res.json();
            this.zones = data.map(z => ({
              ...z,
              areas: [],
              loadingAreas: false,
              areasLoaded: false
            }));
          } catch (e) {
            console.error('Failed to load zones');
          } finally {
            this.loadingZones = false;
          }
        },

        closeModal() {
          this.showModal = false;
          setTimeout(() => {
            this.selectedCity = null;
            this.zones = [];
          }, 300); // Wait for transition
        },

        async toggleZone(zone) {
          if (this.expandedZoneId === zone.zone_id) {
            this.expandedZoneId = null;
            return;
          }
          
          this.expandedZoneId = zone.zone_id;
          
          if (!zone.areasLoaded) {
            zone.loadingAreas = true;
            try {
              const res = await fetch('{{ url("api/pathao/areas") }}/' + zone.zone_id);
              zone.areas = await res.json();
              zone.areasLoaded = true;
            } catch (e) {
              console.error('Failed to load areas');
            } finally {
              zone.loadingAreas = false;
            }
          }
        }
      }));
    });
  </script>

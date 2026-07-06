@once
<script>
    /**
     * PHASE-2.3: generate/reuse the approval link for a mockup.
     * Opens WhatsApp prefilled when the mockup is linked to an order;
     * otherwise copies the public link to the clipboard.
     */
    async function shareMockup(id) {
        try {
            const resp = await fetch(`/mockups/${id}/share`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });
            const data = await resp.json();
            if (!data.success) {
                alert(data.message || 'Could not create the approval link.');
                return;
            }
            if (data.wa_link) {
                window.open(data.wa_link, '_blank');
            } else {
                await navigator.clipboard.writeText(data.url);
                alert('Approval link copied to clipboard:\n' + data.url);
            }
        } catch (e) {
            alert('Network error while creating the approval link.');
        }
    }
</script>
@endonce

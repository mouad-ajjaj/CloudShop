// frontend/assets/js/config.js
const API_BASE_URL = "http://localhost:8000/api";

// Function to dynamically update the "View Store" link with the correct ID
async function fixStoreLink() {
    const token = localStorage.getItem('token');
    if (!token) return;

    try {
        // Ask backend for my store details
        const res = await fetch(`${API_BASE_URL}/vendor/store/me`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });

        if (res.ok) {
            const store = await res.json();
            // Find the "Voir ma boutique" link and update it
            // This looks for any link pointing exactly to "store-public.html"
            const link = document.querySelector('a[href="store-public.html"]');
            if (link) {
                link.href = `store-public.html?id=${store.id}`;
                link.target = "_blank"; // Ensure it opens in new tab
            }
        }
    } catch (e) {
        console.log("Could not update store link", e);
    }
}

// Run this function automatically when any page loads
document.addEventListener('DOMContentLoaded', fixStoreLink);
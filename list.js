/*
 * organizer_list.js
 * This file fetches organizers from 'organizer_list.php'
 * and displays them on the page.
 */

document.addEventListener('DOMContentLoaded', () => {
  const listDiv = document.getElementById("organizerList");
  const loadingText = document.getElementById("loading-text");

  // Get event type from URL
  const params = new URLSearchParams(window.location.search);
  const eventType = params.get("event");

  // Fetch organizers from backend
  async function loadOrganizers() {
    try {
      // --- THIS IS THE CORRECTED FETCH URL ---
      // We call our *own* page, but add '&fetch=1'
      // to tell the PHP to return JSON.
      const response = await fetch(`list.php?event=${encodeURIComponent(eventType)}&fetch=1`);
      
      if (!response.ok) {
        throw new Error(`Server returned ${response.status}`);
      }
      
      const data = await response.json();

      // Clear the "Loading..." text
      listDiv.innerHTML = '';

      // Check if there's an error in the response
      if (data.error) {
        listDiv.innerHTML = `<p style='text-align:center;color:red;'>Database Error: ${data.error}</p>`;
        return;
      }

      // Handle debug response format
      const organizers = data.organizers || data;
      
      // Show debug info
      console.log('Debug Info:', data);

      if (organizers.length === 0) {
        listDiv.innerHTML = `<p style='text-align:center;color:#888;'>No organizers found for "${data.event_type || eventType}". Query executed: ${data.query_executed || 'unknown'}</p>`;
      } else {
        organizers.forEach(org => {
          const li = document.createElement("li");
          
          // Use a placeholder if profile_pic is missing
          const profilePic = org.profile_pic || 'https://placehold.co/100x100/E2E8F0/94A3B8?text=Org';
          
          // --- THESE ARE THE CORRECTED VARIABLES ---
          li.innerHTML = `
            <img src="${profilePic}" alt="${org.page_title}">
            <div class="org-info">
              <a href="organizer_profile_view.php?id=${org.user_id}" class="org-name">${org.page_title}</a>
              ${org.description ? `<p class="org-description">${org.description}</p>` : ''}
              ${org.email ? `<p class="org-contact"><strong>Email:</strong> ${org.email}</p>` : ''}
              ${org.phone ? `<p class="org-contact"><strong>Phone:</strong> ${org.phone}</p>` : ''}
            </div>
            <a href="place_order.php?org_id=${org.user_id}" class="view-profile-btn">Place Order</a>
          `;
          listDiv.appendChild(li);
        });
      }
    } catch (err) {
      console.error("Error loading organizers:", err);
      listDiv.innerHTML = "<p style='text-align:center;color:red;'>Failed to load organizers.</p>";
    }
  }

  // Load the organizers when the page starts
  if (eventType) {
    loadOrganizers();
  } else {
    listDiv.innerHTML = "<p style='text-align:center;color:#888;'>Select an event type to see organizers</p>";
  }
});

function goToOrganizers(eventType) {
  // Redirect to organizers page with event type as query parameter
  window.location.href = `main.html?event=${encodeURIComponent(eventType)}`;
}
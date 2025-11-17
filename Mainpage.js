// --- Background Video Handling ---
const bgVideo = document.getElementById("bgVideo");

const videoSources = {
  wedding: "video/wedding2.mp4",
  corporate: "video/corporate2.mp4",
  birthday: "video/birthday2.mp4",
  concert: "video/concert2.mp4",
  conference: "video/corporate2.mp4",
  kids: "video/birthday2.mp4"
};

const defaultVideo = "video/corporte.mp4";

document.querySelectorAll('.category-card').forEach((card, index) => {
  let key;
  switch (index) {
    case 0: key = "wedding"; break;
    case 1: key = "corporate"; break;
    case 2: key = "birthday"; break;
    case 3: key = "concert"; break;
    case 4: key = "conference"; break;
    case 5: key = "kids"; break;
  }

  card.addEventListener('mouseenter', () => {
    const source = bgVideo.querySelector('source');
    bgVideo.style.opacity = 0;
    setTimeout(() => {
      source.src = videoSources[key];
      bgVideo.load();
      bgVideo.play();
      bgVideo.style.opacity = 1;
    }, 400);
  });

  card.addEventListener('mouseleave', () => {
    const source = bgVideo.querySelector('source');
    bgVideo.style.opacity = 0;
    setTimeout(() => {
      source.src = defaultVideo;
      bgVideo.load();
      bgVideo.play();
      bgVideo.style.opacity = 1;
    }, 400);
  });
});

// --- Redirect to Organizer List Page ---
function viewOrganizers(eventType) {
  // Option 1: Redirect to a dedicated list page
  window.location.href = `list.php?event=${eventType}`;

  // Option 2: (AJAX) Fetch organizers directly
  /*
  fetch(`index.php?fetchOrganizers=1&event=${eventType}`)
    .then(res => res.json())
    .then(data => {
      console.log("Organizers for", eventType, data);
      // You can display them in a modal or new section
    });
  */
}

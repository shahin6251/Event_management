function openPortfolioModal(item) {
  document.getElementById('modalTitle').textContent = item.title;
  
  let images = [];
  let videos = [];
  
  try {
    images = JSON.parse(item.images) || [];
    videos = JSON.parse(item.videos) || [];
  } catch (e) {
    console.error('Error parsing media:', e);
  }
  
  let content = `
    <div class="space-y-8">
      <!-- Event Details Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-6 rounded-xl border border-blue-100">
          <div class="flex items-center mb-3">
            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
              </svg>
            </div>
            <div>
              <h4 class="font-semibold text-blue-900">Event Type</h4>
              <p class="text-blue-700 font-medium">${item.event_type}</p>
            </div>
          </div>
        </div>
        
        <div class="bg-gradient-to-br from-green-50 to-emerald-50 p-6 rounded-xl border border-green-100">
          <div class="flex items-center mb-3">
            <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center mr-3">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a2 2 0 100-4 2 2 0 000 4zm0 0v2a2 2 0 002 2h6a2 2 0 002-2v-2"></path>
              </svg>
            </div>
            <div>
              <h4 class="font-semibold text-green-900">Event Date</h4>
              <p class="text-green-700 font-medium">${new Date(item.event_date).toLocaleDateString('en-US', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
              })}</p>
            </div>
          </div>
        </div>
        
        ${item.client_name ? `
        <div class="bg-gradient-to-br from-purple-50 to-violet-50 p-6 rounded-xl border border-purple-100">
          <div class="flex items-center mb-3">
            <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center mr-3">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
            </div>
            <div>
              <h4 class="font-semibold text-purple-900">Client</h4>
              <p class="text-purple-700 font-medium">${item.client_name}</p>
            </div>
          </div>
        </div>
        ` : ''}
        
        ${item.location ? `
        <div class="bg-gradient-to-br from-orange-50 to-amber-50 p-6 rounded-xl border border-orange-100">
          <div class="flex items-center mb-3">
            <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center mr-3">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </svg>
            </div>
            <div>
              <h4 class="font-semibold text-orange-900">Location</h4>
              <p class="text-orange-700 font-medium">${item.location}</p>
            </div>
          </div>
        </div>
        ` : ''}
      </div>
      
      <!-- Description -->
      <div class="bg-gray-50 p-6 rounded-xl">
        <h4 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
          <svg class="w-6 h-6 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          Event Story
        </h4>
        <p class="text-gray-700 leading-relaxed text-lg">${item.description}</p>
      </div>
  `;
  
  if (images.length > 0) {
    content += `
      <div>
        <h4 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
          <svg class="w-6 h-6 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
          </svg>
          Event Gallery (${images.length} photos)
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    `;
    images.forEach((image, index) => {
      content += `
        <div class="group relative overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
          <img src="${image}" alt="Event photo ${index + 1}" class="w-full h-48 object-cover cursor-pointer transition-transform duration-300 group-hover:scale-110" onclick="window.open('${image}', '_blank')">
          <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300 flex items-center justify-center">
            <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
            </svg>
          </div>
        </div>
      `;
    });
    content += `</div></div>`;
  }
  
  if (videos.length > 0) {
    content += `
      <div>
        <h4 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
          <svg class="w-6 h-6 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
          </svg>
          Event Videos (${videos.length} videos)
        </h4>
        <div class="space-y-6">
    `;
    videos.forEach((video, index) => {
      content += `
        <div class="bg-black rounded-xl overflow-hidden shadow-lg">
          <video controls class="w-full rounded-xl" preload="metadata">
            <source src="${video}" type="video/mp4">
            Your browser does not support the video tag.
          </video>
        </div>
      `;
    });
    content += `</div></div>`;
  }
  
  content += `</div>`;
  
  document.getElementById('modalContent').innerHTML = content;
  
  // Show modal with animation
  const modal = document.getElementById('portfolioModal');
  const container = document.getElementById('modalContainer');
  
  modal.classList.remove('hidden');
  setTimeout(() => {
    container.classList.remove('scale-95', 'opacity-0');
    container.classList.add('scale-100', 'opacity-100');
  }, 10);
}

function closePortfolioModal() {
  const modal = document.getElementById('portfolioModal');
  const container = document.getElementById('modalContainer');
  
  container.classList.remove('scale-100', 'opacity-100');
  container.classList.add('scale-95', 'opacity-0');
  
  setTimeout(() => {
    modal.classList.add('hidden');
  }, 300);
}

// Close modal when clicking outside
document.getElementById('portfolioModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closePortfolioModal();
  }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closePortfolioModal();
  }
});

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all device maps
    const deviceMaps = [];
    document.querySelectorAll('.device-map').forEach((mapElement, index) => {
        const lat = parseFloat(mapElement.dataset.lat);
        const lng = parseFloat(mapElement.dataset.lng);
        const deviceName = mapElement.dataset.deviceName;
        
        if (isNaN(lat) || isNaN(lng)) return;
        
        // Create map instance
        const map = L.map(mapElement, {
            zoomControl: false,
            dragging: false,
            touchZoom: false,
            scrollWheelZoom: false,
            doubleClickZoom: false,
            boxZoom: false,
            tap: false,
            zoom: 15
        });
        
        // Add tile layer (OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);
        
        // Add marker
        const marker = L.marker([lat, lng], {
            title: deviceName,
            alt: deviceName,
            riseOnHover: true
        }).addTo(map);
        
        // Set view to marker position
        map.setView([lat, lng], 15);
        
        // Store map instance for later reference
        deviceMaps.push({
            element: mapElement,
            map: map,
            marker: marker
        });
        
        // Add click handler to open full map in modal
        mapElement.addEventListener('click', function() {
            openFullMap(lat, lng, deviceName);
        });
    });
    
    // Function to open full map in modal
    function openFullMap(lat, lng, title) {
        // Create modal HTML if it doesn't exist
        if (!document.getElementById('mapModal')) {
            const modalHTML = `
                <div class="modal fade" id="mapModal" tabindex="-1" role="dialog" aria-labelledby="mapModalLabel">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <h4 class="modal-title" id="mapModalLabel">${title || 'Konum'}</h4>
                            </div>
                            <div class="modal-body" style="height: 500px; padding: 0;">
                                <div id="fullMap" style="width: 100%; height: 100%;"></div>
                            </div>
                            <div class="modal-footer">
                                <div class="pull-left">
                                    <a href="https://www.google.com/maps?q=${lat},${lng}" 
                                       target="_blank" 
                                       class="btn btn-default btn-xs">
                                        <i class="fa fa-external-link-alt"></i> Google Haritalar'da Aç
                                    </a>
                                    <a href="https://maps.apple.com/?q=${lat},${lng}" 
                                       target="_blank" 
                                       class="btn btn-default btn-xs">
                                        <i class="fa fa-map-marker-alt"></i> Apple Haritalar'da Aç
                                    </a>
                                </div>
                                <button type="button" class="btn btn-default" data-dismiss="modal">Kapat</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHTML);
        }
        
        // Show the modal
        $('#mapModal').modal('show');
        
        // Initialize or update the map
        if (typeof fullMap === 'undefined') {
            fullMap = L.map('fullMap').setView([lat, lng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19
            }).addTo(fullMap);
            
            // Add marker
            fullMapMarker = L.marker([lat, lng], {
                title: title || 'Konum',
                alt: title || 'Konum',
                riseOnHover: true
            }).addTo(fullMap);
        } else {
            fullMap.setView([lat, lng], 15);
            fullMapMarker.setLatLng([lat, lng]);
            fullMapMarker.setTooltipContent(title || 'Konum');
        }
        
        // Update modal title
        if (title) {
            document.querySelector('#mapModal .modal-title').textContent = title;
        }
    }
    
    // Handle modal cleanup
    $('#mapModal').on('hidden.bs.modal', function () {
        // Clean up the map to prevent memory leaks
        if (typeof fullMap !== 'undefined') {
            fullMap.remove();
            fullMap = undefined;
            fullMapMarker = undefined;
        }
        $(this).remove();
    });
    
    // Auto-refresh the page every 30 seconds
    setTimeout(function() {
        window.location.reload();
    }, 30000);
});

function initializeGooglePlacesAutocomplete() {
    // Initialize Google Places Autocomplete
    const addressInput = document.getElementById('address');
    const cityInput = document.getElementById('city');
    const stateInput = document.getElementById('state');
    const zipInput = document.getElementById('zip');
    const countrySelect = document.getElementById('country');

    const latInput = document.getElementById('lat');
    const lonInput = document.getElementById('lon');

    if (!addressInput) return;

    // Initialize the autocomplete feature
    const autocomplete = new google.maps.places.Autocomplete(addressInput, {
        types: ['address'],
        fields: ['address_components', 'formatted_address', 'geometry'] // Added geometry to get lat/lng
    });

    // Handle place selection
    autocomplete.addListener('place_changed', function() {
        const place = autocomplete.getPlace();

        if (!place.address_components) return;

        // Reset form fields
        cityInput.value = '';
        stateInput.value = '';
        zipInput.value = '';
        countrySelect.value = '';
        latInput.value = '';
        lonInput.value = '';

        // Set latitude and longitude if available
        if (place.geometry && place.geometry.location) {
            latInput.value = place.geometry.location.lat();
            lonInput.value = place.geometry.location.lng();
        }

        // Fill in the address fields
        for (const component of place.address_components) {

            const componentType = component.types[0];

            console.log(place);

            switch (componentType) {
                case 'street_number': {
                    const streetNumber = component.long_name;
                    addressInput.value = streetNumber + ' ';
                    break;
                }
                case 'route': {
                    const street = component.long_name;
                    addressInput.value += street;
                    break;
                }
                case 'locality': // City
                    cityInput.value = component.long_name;
                    break;
                case 'administrative_area_level_1': // State
                    stateInput.value = component.long_name;
                    break;
                case 'postal_code': // ZIP code
                    zipInput.value = component.long_name;
                    break;
                case 'country':
                    // Find and select the country option
                    const options = countrySelect.options;
                    for (let i = 0; i < options.length; i++) {
                        if (options[i].text === component.long_name) {
                            countrySelect.selectedIndex = i;
                            break;
                        }
                    }
                    break;
            }
        }
    });

    // Prevent form submission on enter key when selecting an address
    addressInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && document.activeElement === addressInput) {
            e.preventDefault();
        }
    });

    // Optional: Add CSS styles for the Google Places Autocomplete dropdown
    const style = document.createElement('style');
    style.textContent = `
    .pac-container {
    z-index: 10000;
    border-radius: 4px;
    border: 1px solid #ccc;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
    }
    .pac-item {
    padding: 8px 12px;
    cursor: pointer;
    }
    .pac-item:hover {
    background-color: #f5f5f5;
    }
    `;
    document.head.appendChild(style);
}

initializeGooglePlacesAutocomplete();


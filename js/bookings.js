// Taxi Booking System JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const pickupSelect = document.getElementById('pickup_location');
    const destinationSelect = document.getElementById('destination');
    const passengersSelect = document.getElementById('passengers_count');
    const fareDisplay = document.getElementById('fareDisplay');
    const fareAmount = document.getElementById('fareAmount');
    const taxisList = document.getElementById('availableTaxisList');
    const routeButtons = document.querySelectorAll('.route-btn');

    // Route button click handler
    routeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const pickup = this.getAttribute('data-pickup');
            const dest = this.getAttribute('data-dest');
            
            // Update selects
            pickupSelect.value = pickup;
            destinationSelect.value = dest;
            
            // Update UI
            routeButtons.forEach(b => b.classList.remove('selected'));
            this.classList.add('selected');
            
            // Calculate fare and load taxis
            calculateFare();
            loadAvailableTaxis(pickup);
        });
    });

    // Pickup location change handler
    pickupSelect.addEventListener('change', function() {
        if (this.value) {
            loadAvailableTaxis(this.value);
        } else {
            taxisList.innerHTML = '<p>Select pickup location to see available taxis...</p>';
        }
        calculateFare();
    });

    // Destination change handler
    destinationSelect.addEventListener('change', calculateFare);

    // Passengers change handler
    passengersSelect.addEventListener('change', calculateFare);

    // Calculate fare function
    function calculateFare() {
        const pickup = pickupSelect.value;
        const destination = destinationSelect.value;
        const passengers = passengersSelect.value;

        if (!pickup || !destination) {
            fareDisplay.style.display = 'none';
            return;
        }

        // Show loading
        fareAmount.textContent = 'Calculating...';

        // API call to calculate fare
        fetch(`api/bookings.php?action=calculate_fare&pickup=${encodeURIComponent(pickup)}&destination=${encodeURIComponent(destination)}&passengers=${passengers}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    fareAmount.textContent = data.fare.toLocaleString();
                    fareDisplay.style.display = 'block';
                } else {
                    fareDisplay.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error calculating fare:', error);
                fareDisplay.style.display = 'none';
            });
    }

    // Load available taxis
    function loadAvailableTaxis(location) {
        taxisList.innerHTML = '<p>Loading available taxis...</p>';

        fetch(`api/bookings.php?action=get_available_taxis&location=${encodeURIComponent(location)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.taxis.length > 0) {
                    let html = '';
                    data.taxis.forEach(taxi => {
                        html += `
                            <div class="taxi-card">
                                <div class="taxi-info">
                                    <div class="taxi-details">
                                        <h4>${taxi.taxi_number} - ${taxi.model}</h4>
                                        <p>📍 ${taxi.current_location} | 🪑 Capacity: ${taxi.capacity} seats</p>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    taxisList.innerHTML = html;
                } else {
                    taxisList.innerHTML = '<p>No taxis available at this location. Please try another location.</p>';
                }
            })
            .catch(error => {
                console.error('Error loading taxis:', error);
                taxisList.innerHTML = '<p>Error loading taxis. Please try again.</p>';
            });
    }

    // Form submission handler
    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            const pickup = pickupSelect.value;
            const destination = destinationSelect.value;
            
            if (!pickup || !destination) {
                e.preventDefault();
                alert('Please select both pickup and destination locations');
                return;
            }

            if (pickup === destination) {
                e.preventDefault();
                alert('Pickup and destination cannot be the same');
                return;
            }

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = 'Booking...';
            submitBtn.disabled = true;
        });
    }
});
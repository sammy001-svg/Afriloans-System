document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('loan-product');
    const amountInput = document.getElementById('loan-amount');
    const durationInput = document.getElementById('loan-duration');
    
    const amountVal = document.getElementById('amount-val');
    const durationVal = document.getElementById('duration-val');
    
    const resPrincipal = document.getElementById('res-principal');
    const resInterest = document.getElementById('res-interest');
    const resMonthly = document.getElementById('res-monthly');
    const resTotal = document.getElementById('res-total');

    function updateCalculator() {
        const selectedProduct = productSelect.options[productSelect.selectedIndex];
        const rate = parseFloat(selectedProduct.getAttribute('data-rate'));
        const minAmount = parseFloat(selectedProduct.getAttribute('data-min'));
        const maxAmount = parseFloat(selectedProduct.getAttribute('data-max'));
        const maxDuration = parseInt(selectedProduct.getAttribute('data-duration'));

        // Update range constraints based on product
        amountInput.min = minAmount;
        amountInput.max = maxAmount;
        durationInput.max = maxDuration;

        // Current values
        let amount = parseFloat(amountInput.value);
        let duration = parseInt(durationInput.value);

        // Ensure values are within bounds
        if (amount < minAmount) amount = minAmount;
        if (amount > maxAmount) amount = maxAmount;
        if (duration > maxDuration) duration = maxDuration;

        // Display values
        amountVal.innerText = 'KES ' + amount.toLocaleString();
        durationVal.innerText = duration + (duration === 1 ? ' Month' : ' Months');

        // Calculation (Simple Interest: Total = Principal + (Principal * Rate/100 * Time))
        const interest = amount * (rate / 100) * duration;
        const total = amount + interest;
        const monthly = total / duration;

        // Update display
        resPrincipal.innerText = 'KES ' + amount.toLocaleString(undefined, {minimumFractionDigits: 2});
        resInterest.innerText = 'KES ' + interest.toLocaleString(undefined, {minimumFractionDigits: 2});
        resMonthly.innerText = 'KES ' + monthly.toLocaleString(undefined, {minimumFractionDigits: 2});
        resTotal.innerText = 'KES ' + total.toLocaleString(undefined, {minimumFractionDigits: 2});
    }

    // Event listeners
    productSelect.addEventListener('change', updateCalculator);
    amountInput.addEventListener('input', updateCalculator);
    durationInput.addEventListener('input', updateCalculator);

    // Initial call
    updateCalculator();

    // --- Carousel Logic ---
    const track = document.querySelector('.carousel-track');
    const slides = Array.from(track.children);
    const dots = Array.from(document.querySelectorAll('.dot'));
    let currentSlide = 0;

    function moveToSlide(index) {
        track.style.transform = `translateX(-${index * 100}%)`;
        dots.forEach(dot => dot.classList.remove('active'));
        dots[index].classList.add('active');
        currentSlide = index;
    }

    function nextSlide() {
        let next = (currentSlide + 1) % slides.length;
        moveToSlide(next);
    }

    // Auto slide every 5 seconds
    setInterval(nextSlide, 5000);

    // Dot click events
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            moveToSlide(index);
        });
    });
});

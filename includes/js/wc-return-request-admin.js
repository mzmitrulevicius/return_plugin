document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById("image-modal");
    const modalImg = document.getElementById("modal-image");
    const captionText = document.getElementById("caption");
    const images = document.querySelectorAll('.return-image-thumbnail');
    const span = document.getElementsByClassName("close")[0];

    images.forEach(image => {
        image.addEventListener('click', function() {
            modal.style.display = "block";
            modalImg.src = this.src;
            captionText.innerHTML = this.alt;
        });
    });

    span.onclick = function() {
        modal.style.display = "none";
    }

    modal.onclick = function() {
        modal.style.display = "none";
    }
});

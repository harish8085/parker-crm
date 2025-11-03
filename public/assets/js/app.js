$(document).ready(function () {
    $(".dropdown").on("show.bs.dropdown", function () {
        // Dropdown is opening, change the icon to close-arrow-dropdown.svg
        $(this)
            .find(".dropdown-icon")
            .attr("src", "/assets/images/arrow-dropdown.svg");
    });

    $(".dropdown").on("hide.bs.dropdown", function () {
        // Dropdown is closing, change the icon to arrow-dropdown.svg
        $(this)
            .find(".dropdown-icon")
            .attr("src", "/assets/images/close-dropdown-sidebar-icon.svg");
    });
});

document.addEventListener("DOMContentLoaded", function () {
    var sidebarToggleBtn = document.querySelector(".navbar-toggler");
    var sidebarContainerForMobileView = document.querySelector(
        ".sidebar-container-for-mobile-view"
    );

    // Toggle sidebar on button click
    sidebarToggleBtn.addEventListener("click", function () {
        sidebarContainerForMobileView.classList.toggle("sidebar-open");
    });

    // Close sidebar when clicking outside
    document.addEventListener("click", function (event) {
        if (
            !sidebarContainerForMobileView.contains(event.target) &&
            !sidebarToggleBtn.contains(event.target)
        ) {
            // Clicked outside the sidebar and toggle button, close the sidebar
            sidebarContainerForMobileView.classList.remove("sidebar-open");
        }
    });

    // var notificationIcon = document.getElementById("notification-icon");
    // var notificationPopup = document.querySelector(".notification-popup");
    // var closeBtn = notificationPopup.querySelector(".close-btn");

    // notificationIcon.addEventListener("click", function () {
    //     togglePopup();
    // });

    // closeBtn.addEventListener("click", function () {
    //     closePopup();
    // });

    // Close the popup when clicking outside of it
    // document.addEventListener("click", function (event) {
    //     if (
    //         !notificationPopup.contains(event.target) &&
    //         event.target !== notificationIcon
    //     ) {
    //         closePopup();
    //     }
    // });

    // function togglePopup() {
    //     if (notificationPopup.style.display === "block") {
    //         closePopup();
    //     } else {
    //         openPopup();
    //     }
    // }

    // function openPopup() {
    //     notificationPopup.style.display = "block";
    //     notificationPopup.style.animation = "fadeIn 0.5s";
    // }

    // function closePopup() {
    //     notificationPopup.style.animation = "none";
    //     notificationPopup.style.display = "none";
    // }
});

window.showButtonLoader = function (button, text, action) {
    if (action === 'disabled') {
        button.html(text + ' <div class="spinner-border spinner-border-sm ml-1" role="status"> <span class="sr-only"></span> </div>');
        button.prop('disabled', true);
    } else {
        button.html(text);
        button.prop('disabled', false);
    }
};


$(document).ready(function () {
    // ===== Sidebar Toggle (Mobile) =====
    $('.navbar-toggler').on('click', function () {
        $('.sidebar').toggleClass('show');
        $('.sidebar-overlay').toggleClass('show');
    });

    $('.sidebar-overlay').on('click', function () {
        $('.sidebar').removeClass('show');
        $('.sidebar-overlay').removeClass('show');
    });

    // ===== Auto-dismiss alerts =====
    setTimeout(function () {
        $('.alert-dismissible').fadeOut(500, function () {
            $(this).remove();
        });
    }, 5000);

    // ===== Table row click (view details) =====
    $('.table tbody tr[data-href]').on('click', function () {
        window.location = $(this).data('href');
    });

    // ===== Confirm delete =====
    $('.btn-delete').on('click', function (e) {
        if (!confirm('Are you sure you want to delete this item?')) {
            e.preventDefault();
        }
    });

    // ===== Search form auto-submit (optional) =====
    let searchTimer;
    $('input[name="search"]').on('keyup', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            $(this).closest('form').submit();
        }.bind(this), 800);
    });

    // ===== Tooltip initialization =====
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (el) {
        return new bootstrap.Tooltip(el);
    });

    // ===== Sidebar active state =====
    var currentPath = window.location.pathname;
    $('.sidebar .nav-link').each(function () {
        var linkPath = $(this).attr('href');
        if (linkPath && currentPath.indexOf(linkPath.replace('../', '')) !== -1) {
            $(this).addClass('active');
        }
    });

    // ===== Smooth scroll to top =====
    $(window).scroll(function () {
        if ($(this).scrollTop() > 200) {
            $('#scrollTop').fadeIn();
        } else {
            $('#scrollTop').fadeOut();
        }
    });

    $('#scrollTop').on('click', function () {
        $('html, body').animate({ scrollTop: 0 }, 500);
    });
});

// ===== Add scroll-to-top button =====
$(document).ready(function () {
    $('body').append(
        '<button id="scrollTop" class="btn btn-primary rounded-circle position-fixed" style="bottom: 30px; right: 30px; width: 45px; height: 45px; display: none; z-index: 9999; box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);">' +
        '<i class="fas fa-arrow-up"></i>' +
        '</button>'
    );
});

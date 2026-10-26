$(document).ready(function () {
  var app = $.spapp({
    defaultView: "dashboard",
    templateDir: "./views/",
    pageNotFound: "error_404",
  });

  // Every view is registered once here. Milestone 1: static views, no data loading.
  app.route({ view: "login", load: "login.html" });
  app.route({ view: "register", load: "register.html" });
  app.route({ view: "dashboard", load: "dashboard.html" });
  app.route({ view: "workout-log", load: "workout-log.html" });
  app.route({ view: "workout-history", load: "workout-history.html" });
  app.route({ view: "exercise-library", load: "exercise-library.html" });
  app.route({ view: "progress", load: "progress.html" });
  app.route({ view: "profile", load: "profile.html" });
  app.route({ view: "admin-panel", load: "admin-panel.html" });

  app.run();

  // Views without the main navigation (login and register).
  var authViews = ["login", "register"];

  function updateLayout() {
    var currentView = window.location.hash.slice(1) || "dashboard";

    $("body").toggleClass("auth-layout", authViews.indexOf(currentView) !== -1);

    $("#mainNav .nav-link").removeClass("active").removeAttr("aria-current");
    $('#mainNav .nav-link[href="#' + currentView + '"]').addClass("active").attr("aria-current", "page");

    // A link inside a modal can switch the view; close the modal so its backdrop does not stay.
    $(".modal.show").each(function () {
      bootstrap.Modal.getOrCreateInstance(this).hide();
    });

    window.scrollTo(0, 0);
  }

  // Close the collapsed mobile menu after a link is chosen.
  $("#mainNav").on("click", "a.nav-link, a.dropdown-item", function () {
    var menu = bootstrap.Collapse.getInstance(document.getElementById("mainNav"));
    if (menu) {
      menu.hide();
    }
  });

  // Milestone 1: forms are not sent anywhere yet. Prevent the full page reload;
  // a form can name the view to show next with data-next-view.
  $("#spapp").on("submit", "form", function (event) {
    event.preventDefault();

    var modal = $(this).closest(".modal").get(0);
    if (modal) {
      bootstrap.Modal.getOrCreateInstance(modal).hide();
    }

    var nextView = $(this).data("next-view");
    if (nextView) {
      window.location.hash = nextView;
    }
  });

  $(window).on("hashchange", updateLayout);
  updateLayout();
});

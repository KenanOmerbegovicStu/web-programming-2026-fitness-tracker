/*
 * jquery.spapp - minimal hash-based router for single-page applications.
 * Taken from the old project (frontend/assets/js/jquery.spapp.js).
 * Change: a view template is loaded only once, on the first visit; later
 * visits just show the already loaded section again.
 */
(function ($) {
  $.spapp = function (options) {
    var config,
      routes = {};

    config = $.extend(
      {
        defaultView: $("main#spapp > section:last-child").attr("id"),
        templateDir: "./views/",
        pageNotFound: false,
      },
      options
    );

    $("main#spapp > section").each(function () {
      var elm = $(this);
      routes[elm.attr("id")] = {
        view: elm.attr("id"),
        load: elm.data("load"),
        loaded: false,
        onCreate: function () {},
        onReady: function () {},
      };
    });

    this.route = function (options) {
      $.extend(routes[options.view], options);
    };

    var routeChange = function () {
      var id = location.hash.slice(1);
      var route = routes[id];
      var elm = $("main#spapp > section").filter(function () {
        return this.id === id;
      });

      $("main#spapp > section").hide();

      if (!elm.length || !route) {
        if (config.pageNotFound) {
          window.location.hash = config.pageNotFound;
          return;
        }
        console.log(id + " not defined");
        return;
      }

      elm.show();

      if (!route.load || route.loaded) {
        if (!route.loaded) {
          route.loaded = true;
          route.onCreate();
        }
        route.onReady();
      } else {
        $.get(config.templateDir + route.load)
          .done(function (data) {
            elm.html(data);
            route.loaded = true;
            route.onCreate();
            route.onReady();
          })
          .fail(function (xhr, status, error) {
            console.error("Error loading view: " + route.load, error);
            elm.html(
              '<div class="container py-5"><div class="alert alert-danger"><h4>Error loading page</h4><p>Could not load ' +
                route.load +
                '</p><a href="#dashboard" class="btn btn-primary">Return to Dashboard</a></div></div>'
            );
          });
      }
    };

    this.run = function () {
      window.addEventListener("hashchange", function () {
        routeChange();
      });

      if (!window.location.hash) {
        window.location.hash = config.defaultView;
      } else {
        routeChange();
      }
    };

    return this;
  };
})(jQuery);

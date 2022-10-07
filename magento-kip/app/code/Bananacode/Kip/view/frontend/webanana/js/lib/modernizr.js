/*! modernizr 3.5.0 (Custom Build) | MIT *
 * https://modernizr.com/download/?-cssvhunit-cssvwunit-flexbox-touchevents-addtest-setclasses !*/
!(function (e, n, t) {
  function r(e, n) {
    return typeof e === n;
  }
  function o() {
    var e, n, t, o, i, s, l;
    for (var a in C)
      if (C.hasOwnProperty(a)) {
        if (
          ((e = []),
          (n = C[a]),
          n.name &&
            (e.push(n.name.toLowerCase()),
            n.options && n.options.aliases && n.options.aliases.length))
        )
          for (t = 0; t < n.options.aliases.length; t++)
            e.push(n.options.aliases[t].toLowerCase());
        for (o = r(n.fn, "function") ? n.fn() : n.fn, i = 0; i < e.length; i++)
          (s = e[i]),
            (l = s.split(".")),
            1 === l.length
              ? (Modernizr[l[0]] = o)
              : (!Modernizr[l[0]] ||
                  Modernizr[l[0]] instanceof Boolean ||
                  (Modernizr[l[0]] = new Boolean(Modernizr[l[0]])),
                (Modernizr[l[0]][l[1]] = o)),
            w.push((o ? "" : "no-") + l.join("-"));
      }
  }
  function i(e) {
    var n = S.className,
      t = Modernizr._config.classPrefix || "";
    if ((b && (n = n.baseVal), Modernizr._config.enableJSClass)) {
      var r = new RegExp("(^|\\s)" + t + "no-js(\\s|$)");
      n = n.replace(r, "$1" + t + "js$2");
    }
    Modernizr._config.enableClasses &&
      ((n += " " + t + e.join(" " + t)),
      b ? (S.className.baseVal = n) : (S.className = n));
  }
  function s(n, t, r) {
    var o;
    if ("getComputedStyle" in e) {
      o = getComputedStyle.call(e, n, t);
      var i = e.console;
      if (null !== o) r && (o = o.getPropertyValue(r));
      else if (i) {
        var s = i.error ? "error" : "log";
        i[s].call(
          i,
          "getComputedStyle returning null, its possible modernizr test results are inaccurate"
        );
      }
    } else o = !t && n.currentStyle && n.currentStyle[r];
    return o;
  }
  function l() {
    return "function" != typeof n.createElement
      ? n.createElement(arguments[0])
      : b
      ? n.createElementNS.call(n, "http://www.w3.org/2000/svg", arguments[0])
      : n.createElement.apply(n, arguments);
  }
  function a() {
    var e = n.body;
    return e || ((e = l(b ? "svg" : "body")), (e.fake = !0)), e;
  }
  function u(e, t, r, o) {
    var i,
      s,
      u,
      f,
      c = "modernizr",
      d = l("div"),
      p = a();
    if (parseInt(r, 10))
      for (; r--; )
        (u = l("div")), (u.id = o ? o[r] : c + (r + 1)), d.appendChild(u);
    return (
      (i = l("style")),
      (i.type = "text/css"),
      (i.id = "s" + c),
      (p.fake ? p : d).appendChild(i),
      p.appendChild(d),
      i.styleSheet
        ? (i.styleSheet.cssText = e)
        : i.appendChild(n.createTextNode(e)),
      (d.id = c),
      p.fake &&
        ((p.style.background = ""),
        (p.style.overflow = "hidden"),
        (f = S.style.overflow),
        (S.style.overflow = "hidden"),
        S.appendChild(p)),
      (s = t(d, e)),
      p.fake
        ? (p.parentNode.removeChild(p), (S.style.overflow = f), S.offsetHeight)
        : d.parentNode.removeChild(d),
      !!s
    );
  }
  function f(e, n) {
    if ("object" == typeof e) for (var t in e) P(e, t) && f(t, e[t]);
    else {
      e = e.toLowerCase();
      var r = e.split("."),
        o = Modernizr[r[0]];
      if ((2 == r.length && (o = o[r[1]]), "undefined" != typeof o))
        return Modernizr;
      (n = "function" == typeof n ? n() : n),
        1 == r.length
          ? (Modernizr[r[0]] = n)
          : (!Modernizr[r[0]] ||
              Modernizr[r[0]] instanceof Boolean ||
              (Modernizr[r[0]] = new Boolean(Modernizr[r[0]])),
            (Modernizr[r[0]][r[1]] = n)),
        i([(n && 0 != n ? "" : "no-") + r.join("-")]),
        Modernizr._trigger(e, n);
    }
    return Modernizr;
  }
  function c(e, n) {
    return !!~("" + e).indexOf(n);
  }
  function d(e) {
    return e
      .replace(/([a-z])-([a-z])/g, function (e, n, t) {
        return n + t.toUpperCase();
      })
      .replace(/^-/, "");
  }
  function p(e, n) {
    return function () {
      return e.apply(n, arguments);
    };
  }
  function h(e, n, t) {
    var o;
    for (var i in e)
      if (e[i] in n)
        return t === !1
          ? e[i]
          : ((o = n[e[i]]), r(o, "function") ? p(o, t || n) : o);
    return !1;
  }
  function m(e) {
    return e
      .replace(/([A-Z])/g, function (e, n) {
        return "-" + n.toLowerCase();
      })
      .replace(/^ms-/, "-ms-");
  }
  function v(n, r) {
    var o = n.length;
    if ("CSS" in e && "supports" in e.CSS) {
      for (; o--; ) if (e.CSS.supports(m(n[o]), r)) return !0;
      return !1;
    }
    if ("CSSSupportsRule" in e) {
      for (var i = []; o--; ) i.push("(" + m(n[o]) + ":" + r + ")");
      return (
        (i = i.join(" or ")),
        u(
          "@supports (" + i + ") { #modernizr { position: absolute; } }",
          function (e) {
            return "absolute" == s(e, null, "position");
          }
        )
      );
    }
    return t;
  }
  function g(e, n, o, i) {
    function s() {
      u && (delete q.style, delete q.modElem);
    }
    if (((i = r(i, "undefined") ? !1 : i), !r(o, "undefined"))) {
      var a = v(e, o);
      if (!r(a, "undefined")) return a;
    }
    for (
      var u, f, p, h, m, g = ["modernizr", "tspan", "samp"];
      !q.style && g.length;

    )
      (u = !0), (q.modElem = l(g.shift())), (q.style = q.modElem.style);
    for (p = e.length, f = 0; p > f; f++)
      if (
        ((h = e[f]),
        (m = q.style[h]),
        c(h, "-") && (h = d(h)),
        q.style[h] !== t)
      ) {
        if (i || r(o, "undefined")) return s(), "pfx" == n ? h : !0;
        try {
          q.style[h] = o;
        } catch (y) {}
        if (q.style[h] != m) return s(), "pfx" == n ? h : !0;
      }
    return s(), !1;
  }
  function y(e, n, t, o, i) {
    var s = e.charAt(0).toUpperCase() + e.slice(1),
      l = (e + " " + E.join(s + " ") + s).split(" ");
    return r(n, "string") || r(n, "undefined")
      ? g(l, n, o, i)
      : ((l = (e + " " + N.join(s + " ") + s).split(" ")), h(l, n, t));
  }
  function _(e, n, r) {
    return y(e, t, t, n, r);
  }
  var w = [],
    C = [],
    x = {
      _version: "3.5.0",
      _config: {
        classPrefix: "",
        enableClasses: !0,
        enableJSClass: !0,
        usePrefixes: !0,
      },
      _q: [],
      on: function (e, n) {
        var t = this;
        setTimeout(function () {
          n(t[e]);
        }, 0);
      },
      addTest: function (e, n, t) {
        C.push({ name: e, fn: n, options: t });
      },
      addAsyncTest: function (e) {
        C.push({ name: null, fn: e });
      },
    },
    Modernizr = function () {};
  (Modernizr.prototype = x), (Modernizr = new Modernizr());
  var S = n.documentElement,
    b = "svg" === S.nodeName.toLowerCase(),
    T = x._config.usePrefixes
      ? " -webkit- -moz- -o- -ms- ".split(" ")
      : ["", ""];
  x._prefixes = T;
  var z = (x.testStyles = u);
  Modernizr.addTest("touchevents", function () {
    var t;
    if ("ontouchstart" in e || (e.DocumentTouch && n instanceof DocumentTouch))
      t = !0;
    else {
      var r = [
        "@media (",
        T.join("touch-enabled),("),
        "heartz",
        ")",
        "{#modernizr{top:9px;position:absolute}}",
      ].join("");
      z(r, function (e) {
        t = 9 === e.offsetTop;
      });
    }
    return t;
  }),
    z("#modernizr { height: 50vh; }", function (n) {
      var t = parseInt(e.innerHeight / 2, 10),
        r = parseInt(s(n, null, "height"), 10);
      Modernizr.addTest("cssvhunit", r == t);
    }),
    z("#modernizr { width: 50vw; }", function (n) {
      var t = parseInt(e.innerWidth / 2, 10),
        r = parseInt(s(n, null, "width"), 10);
      Modernizr.addTest("cssvwunit", r == t);
    });
  var P;
  !(function () {
    var e = {}.hasOwnProperty;
    P =
      r(e, "undefined") || r(e.call, "undefined")
        ? function (e, n) {
            return n in e && r(e.constructor.prototype[n], "undefined");
          }
        : function (n, t) {
            return e.call(n, t);
          };
  })(),
    (x._l = {}),
    (x.on = function (e, n) {
      this._l[e] || (this._l[e] = []),
        this._l[e].push(n),
        Modernizr.hasOwnProperty(e) &&
          setTimeout(function () {
            Modernizr._trigger(e, Modernizr[e]);
          }, 0);
    }),
    (x._trigger = function (e, n) {
      if (this._l[e]) {
        var t = this._l[e];
        setTimeout(function () {
          var e, r;
          for (e = 0; e < t.length; e++) (r = t[e])(n);
        }, 0),
          delete this._l[e];
      }
    }),
    Modernizr._q.push(function () {
      x.addTest = f;
    });
  var j = "Moz O ms Webkit",
    E = x._config.usePrefixes ? j.split(" ") : [];
  x._cssomPrefixes = E;
  var N = x._config.usePrefixes ? j.toLowerCase().split(" ") : [];
  x._domPrefixes = N;
  var k = { elem: l("modernizr") };
  Modernizr._q.push(function () {
    delete k.elem;
  });
  var q = { style: k.elem.style };
  Modernizr._q.unshift(function () {
    delete q.style;
  }),
    (x.testAllProps = y),
    (x.testAllProps = _),
    Modernizr.addTest("flexbox", _("flexBasis", "1px", !0)),
    o(),
    i(w),
    delete x.addTest,
    delete x.addAsyncTest;
  for (var A = 0; A < Modernizr._q.length; A++) Modernizr._q[A]();
  e.Modernizr = Modernizr;
})(window, document);

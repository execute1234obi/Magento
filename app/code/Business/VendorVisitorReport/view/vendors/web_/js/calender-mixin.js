define(
     [
         'jquery',
         'underscore',
         'ko',
         'uiComponent',
         'uiRegistry',
     ],
     function (
         $,
         _,
         ko,
         Component,
         registry,
     ) {
         'use strict';

        var mixin = {
             method1: function(datesArr) { alert("asasas")/* my code */ },
        };

        return function (target) {
            return target.extend(mixin);
        };

    }
);

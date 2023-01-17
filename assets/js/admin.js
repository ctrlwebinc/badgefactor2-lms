/*
 * Badge Factor 2
 * Copyright (C) 2019 ctrlweb
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
jQuery(document).ready(function ($) {
    if ($('input[name="status"]').val() !== "rejected") {
        $(
            ".cmb-type-badge-request-rejection-reason, .cmb-type-badge-request-revision-reason"
        ).hide();
    } else {
        $(
            ".cmb-type-badge-request-revision-reason"
        ).hide();
    }
    function insertParam(key, value) {
        key = encodeURI(key);
        value = encodeURI(value);
        var kvp = document.location.search.substr(1).split("&");
        var i = kvp.length;
        var x;
        while (i--) {
            x = kvp[i].split("=");

            if (x[0] == key) {
                x[1] = value;
                kvp[i] = x.join("=");
                break;
            }
        }
        if (i < 0) {
            kvp[kvp.length] = [key, value].join("=");
        }
        return kvp.join("&");
    }
    function removeParam(key) {
        var sourceURL = window.location.href;
        var rtn = sourceURL.split("?")[0],
            param,
            params_arr = [],
            queryString =
                sourceURL.indexOf("?") !== -1 ? sourceURL.split("?")[1] : "";
        if (queryString !== "") {
            params_arr = queryString.split("&");
            for (var i = params_arr.length - 1; i >= 0; i -= 1) {
                param = params_arr[i].split("=")[0];
                if (param === key) {
                    params_arr.splice(i, 1);
                }
            }
            rtn = rtn + "?" + params_arr.join("&");
        }
        return rtn;
    }

    $("body")
        .on("click", "button.notice-dismiss", function () {
            var url = new URL(window.location.href);
            url.searchParams.delete("notice");
            window.history.pushState({}, "", url);
        })
        .on("click", "button#approve-badge", function (e) {
            e.preventDefault();
            $(".cmb-type-badge-request-rejection-reason").fadeOut();
            $(".cmb-type-badge-request-revision-reason").fadeOut();
            var button = $(this);
            var action_buttons = $(this)
                .closest(".button-group")
                .find("button");
            var form = $(this).closest("form");
            if (confirm(button.data("confirm"))) {
                action_buttons.attr("disabled", true);
                var post_id = form.find("input#post_ID").val();

                $.post(
                    ajaxurl,
                    {
                        action: "approve_badge_request",
                        badge_request_id: post_id
                    },
                    function (response) {
                        location.reload();
                    }
                );
            }

            return false;
        })
        .on("click", "button#start-badge-rejection", function (e) {
            e.preventDefault();
            $(".cmb-type-badge-request-revision-reason").fadeOut(function () {
                $(".cmb-type-badge-request-rejection-reason").fadeIn();
            });
            return false;
        })
        .on("click", "button#reject-badge", function (e) {
            e.preventDefault();
            var button = $(this);
            var action_buttons = $(this)
                .closest(".button-group")
                .find("button");
            var form = $(this).closest("form");
            if (confirm(button.data("confirm"))) {
                action_buttons.attr("disabled", true);
                var post_id = form.find("input#post_ID").val(),
                    rejection_reason = form.find("#rejection_reason").val();

                $.post(
                    ajaxurl,
                    {
                        action: "reject_badge_request",
                        badge_request_id: post_id,
                        rejection_reason: rejection_reason
                    },
                    function (response) {
                        location.reload();
                    }
                );
            }
            return false;
        })
        .on("click", "button#cancel-reject-badge", function (e) {
            e.preventDefault();
            var button = $(this);
            var action_buttons = $(this)
                .closest(".button-group")
                .find("button");
            var form = $(this).closest("form");
            if (confirm(button.data("confirm"))) {
                action_buttons.attr("disabled", true);
                var post_id = form.find("input#post_ID").val(),

                $.post(
                    ajaxurl,
                    {
                        action: "cancel-reject_badge_request",
                        badge_request_id: post_id,
                    },
                    function (response) {
                        location.reload();
                    }
                );
            }
            return false;
        })
        .on("click", "button#start-badge-revision", function (e) {
            e.preventDefault();
            $(".cmb-type-badge-request-rejection-reason").fadeOut(function () {
                $(".cmb-type-badge-request-revision-reason").fadeIn();
            });
            return false;
        })
        .on("click", "button#revise-badge", function (e) {
            e.preventDefault();
            var button = $(this);
            var action_buttons = $(this)
                .closest(".button-group")
                .find("button");
            var form = $(this).closest("form");
            if (confirm(button.data("confirm"))) {
                action_buttons.attr("disabled", true);
                var post_id = form.find("input#post_ID").val(),
                    revision_reason = form.find("#revision_reason").val();

                $.post(
                    ajaxurl,
                    {
                        action: "revise_badge_request",
                        badge_request_id: post_id,
                        revision_reason: revision_reason
                    },
                    function (response) {
                        location.reload();
                    }
                );
            }
            return false;
        })
        .on("click", "button#cancel-revise-badge", function (e) {
            e.preventDefault();
            var button = $(this);
            var action_buttons = $(this)
                .closest(".button-group")
                .find("button");
            var form = $(this).closest("form");
            if (confirm(button.data("confirm"))) {
                action_buttons.attr("disabled", true);
                var post_id = form.find("input#post_ID").val(),

                $.post(
                    ajaxurl,
                    {
                        action: "cancel_revise_badge_request",
                        badge_request_id: post_id,
                        revision_reason: revision_reason
                    },
                    function (response) {
                        location.reload();
                    }
                );
            }
            return false;
        });
    $(document).on(
        "change",
        "#bf2-admin-filter select[name='filter_type'], #bf2-admin-filter select[name='filter_value']",
        function () {
            var url = new URL(window.location.href),
                name = $(this).attr("name"),
                value = $(this).val();
            url.searchParams.set(name, value);
            if ("filter_type" === name) {
                url.searchParams.delete("filter_value");
            }
            window.location.href = url;
        }
    );
    $("#menu-posts-badge-page img").each(function () {
        var $img = $(this);
        var imgID = $img.attr("id");
        var imgClass = $img.attr("class");
        var imgURL = $img.attr("src");
        $.get(
            imgURL,
            function (data) {
                // Get the SVG tag, ignore the rest
                var $svg = $(data).find("svg");
                // Add replaced image's ID to the new SVG
                if (typeof imgID !== "undefined") {
                    $svg = $svg.attr("id", imgID);
                }
                // Add replaced image's classes to the new SVG
                if (typeof imgClass !== "undefined") {
                    $svg = $svg.attr("class", imgClass + " replaced-svg");
                }
                // Remove any invalid XML tags as per http://validator.w3.org
                $svg = $svg.removeAttr("xmlns:a");
                // Replace image with new SVG
                $img.replaceWith($svg);
            },
            "xml"
        );
    });
});

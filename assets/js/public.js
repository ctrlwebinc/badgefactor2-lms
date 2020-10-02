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
    $(document).on("submit", "#badge-request-form", function (e) {
        var $form = $(this);
        e.preventDefault();
        $("body").css("cursor", "progress");
        $.post(ajaxurl, $form.serialize(), function (response) {
            var message_class = "error";
            if (response.success === true) {
                message_class = "success";
            }
            $form
                .find("input[type='submit']")
                .replaceWith(
                    "<p class='" + message_class + "'>" + response.message + "</p>"
                );
            $("body").css("cursor", "default");
        });
    });
});

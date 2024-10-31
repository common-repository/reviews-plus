/*!
 impleCode Product Reviews Scripts v1.0.0

 (c) 2022 Norbert Dreszer - https://implecode.com
 */

jQuery(document).ready(function () {
    /* global ic_revs */
    jQuery(document).on('ic_tabs_initialized', function () {
        if (window.location.hash.indexOf('comment') >= 0 || window.location.hash.indexOf('respond') >= 0 || window.location.hash.indexOf('review') >= 0) {
            jQuery('.boxed .after-product-details .ic_tabs > h3[data-tab_id="ic_revs"]').trigger('click');
        }
    });
    jQuery('.review-rating.allow-edit > span').click(function () {
        var rating = jQuery(this).data('rating');
        ic_apply_rating(rating, jQuery(this));
        jQuery(this).parent('p.review-rating').find('input[name="ic_review_rating"]').val(rating);
    });
    jQuery('.review-rating.allow-edit > span').hover(function () {
        var rating = jQuery(this).data('rating');
        ic_apply_rating(rating, jQuery(this));
    }, function () {
        var rating = jQuery(this).parent('p.review-rating').find('input[name="ic_review_rating"]').val();
        ic_apply_rating(rating, jQuery(this));
    });
    jQuery('#product_reviews').on('submit', '.comment-form', function (e) {
        if (ic_validate_review(jQuery(this), 1)) {
            e.preventDefault();
        }
    });
    jQuery('#product_reviews form:visible').on('click keyup', '*', function () {
        ic_validate_review(jQuery('#product_reviews form:visible'));
    });
});

function ic_validate_review(form, force) {
    if (force === undefined && form.find('.ic-invalid').length === 0) {
        return;
    }
    /* global ic_revs */
    var error = false;
    form.find('.ic-invalid').removeClass('ic-invalid');
    form.find('.al-box.warning').remove();
    var rating_field = form.find('[name="ic_review_rating"]');
    if (rating_field.length && rating_field.val() === '') {
        form.find('.review-rating').before(ic_revs.no_rating);
        form.find('.review-rating').addClass('ic-invalid');
        error = true;
    }
    form.find('[aria-required="true"]').each(function () {
        if (jQuery(this).val() === '') {
            jQuery(this).addClass('ic-invalid');
            jQuery(this).after(ic_revs.no_empty);
            error = true;
        }
    });
    if (error) {
        jQuery('#product_reviews .form-submit').append(ic_revs.check_errors);
    }
    return error;
}

function ic_apply_rating(rating, obj) {
    obj.parent('p.review-rating.allow-edit').find("span").removeClass('rating-on');
    for (var i = 1; i <= rating; i++) {
        obj.parent('p.review-rating.allow-edit').find('span.rate-' + i).addClass('rating-on');
    }
    var off_rating = 5 - rating;
    for (i = 1; i <= off_rating; i++) {
        var a = i + rating;
        obj.parent('p.review-rating.allow-edit').find('span.rate-' + a).addClass('rating-off');
    }
}
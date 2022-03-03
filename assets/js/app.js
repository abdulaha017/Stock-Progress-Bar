; (function ($) {
    "use strict";
    
    jQuery(document).ready(function ($) {
        console.log(el_key_parms);
        $('input.variation_id').change(function () {
            if ('' != $(this).val()) {
                let var_id = $(this).val();
                // alert('You just selected variation #' + var_id);

                $.ajax({
                    url: el_key_parms.ajax_url,
                    type: 'post',
                    dataType: 'json',
                    data: {
                        action: 'variation_ajax_action',
                        var_id: var_id,
                        //  key: '124',
                        // nonce: el_key_parms.ajax_nonce
                    },
                    success: function (res) {
                        console.log(res.data);
                        $('#only-for-va').fadeOut();
                        $('#only-for-va').fadeIn();
                        // $('#only-for-va').find('.current-stock span').append(res.data);
                        // $('#only-for-va').find('.progress').attr("data-percent", res.data);
                        // $(".progress-bar").ProgressBar();

                    },
                    error: function () {
                        console.log('Ajax Error');
                    }
                });
            }
        });

    });

    $(document).ready(function () {

        $(document).ready(function () {
            $(".progress-bar").ProgressBar();
        });
    });

})(jQuery);

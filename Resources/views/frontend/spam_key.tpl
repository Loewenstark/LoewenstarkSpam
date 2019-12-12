<script>
    function checkVariable()
    {
        if ( window.jQuery ){
            if(jQuery('[name="loes_id"]').length==0)
            {
                jQuery('#register--form, #opc-register').append('<input type="hidden" name="loes_id" value="{$loewenstark_spam_key}" />');
            }
        }
        else{
            window.setTimeout("checkVariable();", 1000);
        }
    }
    checkVariable();
</script>
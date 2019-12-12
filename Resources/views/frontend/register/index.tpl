{extends file="parent:frontend/register/index.tpl"}

{block name='frontend_register_index_form_submit'}
    {$smarty.block.parent}
    {include file="frontend/spam_key.tpl"}
{/block}
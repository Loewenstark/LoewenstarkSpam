{extends file="parent:frontend/register/error_message.tpl"}

{block name='frontend_register_error_messages'}
    {$smarty.block.parent}
    {include file="frontend/spam_key.tpl"}
{/block}
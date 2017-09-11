<div class="control-group">
    <label class="control-label" for="invoicebox_participant_id">{__("invoicebox_participant_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][invoicebox_participant_id]" id="invoicebox_participant_id" value="{$processor_params.invoicebox_participant_id}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="invoicebox_participant_ident">{__("invoicebox_participant_ident")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][invoicebox_participant_ident]" id="invoicebox_participant_ident" value="{$processor_params.invoicebox_participant_ident}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="invoicebox_api_key">{__("invoicebox_api_key")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][invoicebox_api_key]" id="invoicebox_api_key" value="{$processor_params.invoicebox_api_key}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="invoicebox_testmode">{__("invoicebox_testmode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][invoicebox_testmode]" id="invoicebox_testmode">
            <option value="test" {if $processor_params.invoicebox_testmode == "On"}selected="selected"{/if}>On</option>
            <option value="live" {if $processor_params.invoicebox_testmode == "Off"}selected="selected"{/if}>Off</option>
        </select>
    </div>
</div>
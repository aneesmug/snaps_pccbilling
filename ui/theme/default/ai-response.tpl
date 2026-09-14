{extends file="$layouts_admin"}

{block name="head"}

{/block}



{block name="content"}

    <div class="panel" id="ai_response_panel" data-panel-lock="false" data-panel-close="false" data-panel-collapsed="false"  data-panel-locked="false" data-panel-refresh="false" data-panel-reset="false">
        <div class="panel-hdr">

            <h2 class="fw-bolder">
                {$response->title}
            </h2>

            <div class="panel-toolbar">
                <a href="{$_url}ai/responses" class="btn btn-primary btn-sm"><i class="fal fa-long-arrow-left"></i> {$_L['Back']}</a>
            </div>

        </div>
        <div class="panel-container">
            <div class="panel-content">
                {appMarkdownRender($response->reply)}
            </div>
        </div>
    </div>

{/block}

{block name="script"}

{/block}



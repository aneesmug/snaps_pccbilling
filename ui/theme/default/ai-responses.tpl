{extends file="$layouts_admin"}

{block name="head"}

{/block}



{block name="content"}

    <div class="panel">
        <div class="panel-hdr">
            <h3 class="card-title">{__('AI Responses')}</h3>
        </div>
        <div class="panel-container">
            <div class="panel-content">

                <table class="table table-condensed table-hover table-bordered" id="clx_datatable">
                    <thead>
                    <tr>
                        <th>{$_L['Title']}</th>
                        <th>{__('Created')}</th>
                        <th class="text-end">{$_L['View']}</th>
                    </tr>
                    </thead>
                    <tbody>

                    {foreach $responses as $response}
                        <tr>

                            <td>
                                <a href="{$_url}ai/response/{$response->uuid}">{$response->title}</a>
                            </td>
                            <td>{$response->created_at->format('D, M d Y h:i a')}</td>
                            <td class="text-end">
                                <a href="{$_url}ai/response/{$response->uuid}" class="btn btn-sm btn-dark">{$_L['View']}</a>
                                <a href="javascript:;" class="btn btn-danger btn-sm" onclick="confirmThenGoToUrl(event,'ai/delete/response/{$response->id}')" data-bs-toggle="tooltip" data-placement="top" title="{$_L['Delete']}"><i class="fal fa-trash-alt"></i></a>
                            </td>

                        </tr>
                    {/foreach}

                    </tbody>
                </table>

            </div>
        </div>
    </div>

{/block}

{block name="script"}
    <script>
        $(function () {
            $('#clx_datatable').dataTable(
                {
                    responsive: true,
                    "language": {
                        "emptyTable": "{$_L['No items to display']}",
                        "info":      "{$_L['Showing _START_ to _END_ of _TOTAL_ entries']}",
                        "infoEmpty":      "{$_L['Showing 0 to 0 of 0 entries']}",
                        buttons: {
                            pageLength: '{$_L['Show all']}'
                        },
                        searchPlaceholder: "{__('Search')}"
                    },
                }
            );
        });
    </script>
{/block}



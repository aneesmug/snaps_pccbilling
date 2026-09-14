{extends file="$layouts_client"}



{block name="content"}

    <div class="panel">
        <div class="panel-hdr">
            <h2 class="h2">{$_L['Total']} : {$total_quotes}</h2>
        </div>
        <div class="panel-container">
            <div class="panel-content">

                <div class="table-responsive">
                    <table class="table  table-striped table-hover sys_table">
                        <thead>
                        <tr>

                            <th width="30%">{$_L['Subject']}</th>
                            <th>{$_L['Amount']}</th>
                            <th>{$_L['Date Created']}</th>
                            <th>{$_L['Expiry Date']}</th>
                            {*<th>{$_L['Stage']}</th>*}

                            <th class="text-end">{$_L['Manage']}</th>
                        </tr>
                        </thead>
                        <tbody>

                        {foreach $d as $ds}
                            <tr>

                                <td class="h5"><a href="{$_url}client/q/{$ds['id']}/token_{$ds['vtoken']}/" target="_blank">{$ds['subject']}</a> </td>
                                <td class="amount">{$ds['total']}</td>
                                <td>{date( $config['df'], strtotime($ds['datecreated']))}</td>
                                <td>{date( $config['df'], strtotime($ds['validuntil']))}</td>


                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="{$_url}client/q/{$ds['id']}/token_{$ds['vtoken']}/" target="_blank" class="btn btn-primary"><i class="fal fa-check"></i></a>

                                        <a href="{$_url}client/qpdf/{$ds['id']}/token_{$ds['vtoken']}/dl/" class="btn btn-success " ><i class="fal fa-file"></i> </a>
                                        <a href="{$_url}client/qpdf/{$ds['id']}/token_{$ds['vtoken']}/" target="_blank" class="btn btn-warning"><i class="fal fa-print"></i> </a>
                                    </div>
                                </td>
                            </tr>

                            {foreachelse}

                            <tr>
                                <td colspan="7">
                                    {$_L['No Data Available']}
                                </td>
                            </tr>

                        {/foreach}

                        </tbody>
                    </table>
                </div>


            </div>

        </div>

    </div>

{/block}

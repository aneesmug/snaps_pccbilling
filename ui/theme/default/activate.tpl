{extends file="$layouts_admin"}

{block name="head"}

{/block}


{block name="content"}

   <div class="row">
       <div class="col-md-6">
           <div class="card">
               <div class="card-body">
                   <form method="post" action="/settings/activate">
                       <div class="mb-3">
                           <label for="purchase_key" class="form-label">Purchase Key</label>
                            <input type="text" class="form-control" id="purchase_key" name="purchase_key" value="{$config['purchase_key']|default: ''}">
                       </div>

                       <button type="submit" class="btn btn-primary">Activate</button>

                   </form>
               </div>
           </div>
       </div>
   </div>

{/block}

{block name="script"}

{/block}



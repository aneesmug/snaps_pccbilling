{extends file="$layouts_admin"}

{block name="head"}
    {if !empty($config['google_maps_api_key'])}
        <script>
            (g => {
                var h, a, k, p = "The Google Maps JavaScript API", c = "google", l = "importLibrary", q = "__ib__",
                    m = document, b = window;
                b = b[c] || (b[c] = { });
                var d = b.maps || (b.maps = {}), r = new Set, e = new URLSearchParams,
                    u = () => h || (h = new Promise(async (f, n) => {
                        await (a = m.createElement("script"));
                        e.set("libraries", [...r] + "");
                        for (k in g) e.set(k.replace(/[A-Z]/g, t => "_" + t[0].toLowerCase()), g[k]);
                        e.set("callback", c + ".maps." + q);
                        {literal}
                        a.src = `https://maps.${c}apis.com/maps/api/js?` + e;
                        {/literal}


                        d[q] = f;
                        a.onerror = () => h = n(Error(p + " could not load."));
                        a.nonce = m.querySelector("script[nonce]")?.nonce || "";
                        m.head.append(a)
                    }));
                d[l] ? console.warn(p + " only loads once. Ignoring:", g) : d[l] = (f, ...n) => r.add(f) && u().then(() => d[l](f, ...n))
            })({
                key: "{$config['google_maps_api_key']}",
                v: "weekly",
            });
        </script>
        <style>
            #map {
                height: calc(100vh - 114px);
                width: 100%;
            }

            .map-container {
                width: 100%;
                margin: 20px auto;
                padding: 0 20px;
            }

            .customer-info {
                padding: 4px;
                max-width: 200px;
            }
            #clx-page-content{
                padding: 0;
            }
        </style>
    {/if}
{/block}



{block name="content"}
    <div class="card rounded-0">
        <div class="card-body">
            <a class="btn btn-dark btn-sm me-2 border-0" href="{$base_url}contacts/map-view">{__('All Contacts')}</a>
            {foreach $groups as $group}
                <a class="btn btn-primary btn-sm me-2 border-0" style="background-color: {$group->color|default:'#3979FF'};" href="{$base_url}contacts/map-view/{$group->id}">{$group->gname}</a>
            {/foreach}
        </div>
    </div>
    <div id="map">

    </div>
{/block}

{block name="script"}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const mapElement = document.getElementById('map');

            mapElement.innerHTML = '<div class="p-3 text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';

            axios.get(base_url + 'contacts/map-data/{$selected_group_id}').then(response => {
                const contacts = response.data;

                async function initMap() {
                    // Request needed libraries.
                    const { Map } = await google.maps.importLibrary("maps");
                    const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");

                    // Create map centered on US
                    const map = new Map(mapElement, {
                        zoom: {$config['contacts_map_zoom']|default:5},
                        center: { lat: {$config['contacts_map_center_latitude']|default:39.8283}, lng: {$config['contacts_map_center_longitude']|default:-98.5795} },
                        mapId: 'APP_MAP_ID',
                    });

                    map.addListener('center_changed', () => {
                        const newCenter = map.getCenter();
                        const lat = newCenter.lat();
                        const lng = newCenter.lng();

                        // Make HTTP request to store the new center
                        axios.post(base_url + 'contacts/map-update-center', {
                            latitude: lat,
                            longitude: lng,
                        })
                            .then(response => {
                                console.log('Map center updated successfully', response.data);
                            })
                            .catch(error => {
                                console.error('Error updating map center', error);
                            });
                    });

                    // For zooming in and out
                    map.addListener('zoom_changed', () => {
                        const zoom = map.getZoom();
                        const zoomLevel = zoom.toFixed(2);

                        // Make HTTP request to store the new zoom level
                        axios.post(base_url + 'contacts/map-update-zoom', {
                            zoom: zoomLevel,
                        })
                            .then(response => {
                                console.log('Map zoom updated successfully', response.data);
                            })
                            .catch(error => {
                                console.error('Error updating map zoom', error);
                            });
                    });

                    // Add markers for each customer
                    contacts.forEach(contact => {

                        const lat = parseFloat(contact.lat);
                        const lng = parseFloat(contact.lon);
                        const color = contact.color || '#3979FF';


                        const customElement = document.createElement('span');
                        customElement.className = 'clx-avatar';
                        customElement.innerHTML = `${ contact.image || '👤' }`;
                        customElement.style.color = color;

                        const marker = new AdvancedMarkerElement({
                            map: map,
                            position: { lat: lat, lng: lng },
                            title: contact.account,
                            content: customElement,
                        });

                        // Create info window content
                        {literal}
                        const infoWindow = new google.maps.InfoWindow({
                            content: `
                        <div class="customer-info">
                            <h3>${contact.account}</h3>
                            <div class="mb-2">${contact.email}</div>
                            <div class="mb-2">${contact.phone}</div>
                            <div class="mb-2">${contact.address}</div>
                        </div>
                    `
                        });
                        {/literal}

                        // Add click listener to show info window
                        marker.addListener('click', () => {
                            infoWindow.open(map, marker);
                        });
                    });
                }

                initMap();

            });


        });
    </script>
{/block}



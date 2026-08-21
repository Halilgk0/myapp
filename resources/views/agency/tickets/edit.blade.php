@extends('layouts.agency')

@section('title', 'Bilet Düzenle')

@section('content')
<div class="ag-page-header ag-flex ag-justify-between ag-items-center ag-mb-3" style="flex-wrap:wrap;gap:16px">
    <div>
        <h1 class="ag-page-title">Bilet Düzenle</h1>
        <p class="ag-page-subtitle">Bilet bilgilerini güncelleyin</p>
    </div>
    <a href="{{ route('agency.tickets.index') }}" class="ag-btn ag-btn-secondary ag-btn-sm">
        <i data-lucide="arrow-left"></i>
        <span>Geri</span>
    </a>
</div>
<div class="container-fluid">
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('agency.tickets.update', $ticket) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Ticket Info (readonly) -->
            <div class="col-md-4">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle"></i> Bilet Bilgileri</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-5">Takip No:</dt>
                            <dd class="col-sm-7"><strong>{{ $ticket->tracking_no }}</strong></dd>
                            
                            <dt class="col-sm-5">Voucher No:</dt>
                            <dd class="col-sm-7">{{ $ticket->voucher_no }}</dd>
                            
                            <dt class="col-sm-5">Tur:</dt>
                            <dd class="col-sm-7">{{ $ticket->tour_name }}</dd>
                            
                            <dt class="col-sm-5">Tur Tarihi:</dt>
                            <dd class="col-sm-7">{{ $ticket->tour_date?->format('d.m.Y') }}</dd>
                            
                            <dt class="col-sm-5">Toplam:</dt>
                            <dd class="col-sm-7">
                                @php
                                    $curr = strtoupper($ticket->currency ?? 'TRY');
                                    $currClass = match($curr) {
                                        'TRY' => 'badge-success',
                                        'EUR' => 'badge-primary',
                                        'USD' => 'badge-danger',
                                        'GBP' => 'badge-gbp',
                                        'RUB' => 'badge-rub',
                                        default => 'badge-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $currClass }}">
                                    {{ number_format($ticket->total_price, 2) }} {{ $curr }}
                                </span>
                            </dd>
                        </dl>

                        <hr>
                        <p class="text-muted small">
                            <i class="fas fa-info-circle"></i> Tur, tarih ve fiyat bilgileri değiştirilemez.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Editable Customer Info -->
            <div class="col-md-8">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user"></i> Müşteri Bilgileri</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer_name"><i class="fas fa-user text-primary"></i> Müşteri Adı <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('customer_name') is-invalid @enderror" 
                                           id="customer_name" name="customer_name" 
                                           value="{{ old('customer_name', $ticket->customer_name) }}" required>
                                    @error('customer_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer_phone"><i class="fas fa-phone text-success"></i> Telefon <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('customer_phone') is-invalid @enderror" 
                                           id="customer_phone" name="customer_phone" 
                                           value="{{ old('customer_phone', $ticket->customer_phone) }}" required>
                                    @error('customer_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer_email"><i class="fas fa-envelope text-info"></i> E-posta</label>
                                    <input type="email" class="form-control @error('customer_email') is-invalid @enderror" 
                                           id="customer_email" name="customer_email" 
                                           value="{{ old('customer_email', $ticket->customer_email) }}">
                                    @error('customer_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer_nationality"><i class="fas fa-flag text-warning"></i> Uyruk <span class="text-danger">*</span></label>
                                    <select class="form-control @error('customer_nationality') is-invalid @enderror" 
                                            id="customer_nationality" name="customer_nationality" required>
                                        @foreach(\App\Models\Ticket::getNationalityOptions() as $code => $name)
                                            <option value="{{ $code }}" {{ old('customer_nationality', $ticket->customer_nationality) == $code ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_nationality')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pickup_location"><i class="fas fa-map-marker-alt text-danger"></i> Alış Noktası</label>
                                    <div class="pickup-search-wrap">
                                        <input type="text" class="form-control @error('pickup_location') is-invalid @enderror"
                                               id="pickup_location" name="pickup_location"
                                               value="{{ old('pickup_location', $ticket->pickup_location) }}"
                                               placeholder="Adres veya otel ara (servis alanı içinde)" autocomplete="off">
                                        <div class="pickup-suggestions" id="pickup-suggestions" role="listbox"></div>
                                    </div>
                                    @error('pickup_location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div style="position:relative;">
                                        <div id="pickup-map" style="height:220px;width:100%;border:1px solid #ced4da;border-radius:4px;margin-top:8px;"></div>
                                        <div id="map-toast" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);z-index:10;background:rgba(30,30,40,.88);color:#fff;padding:10px 20px;border-radius:8px;font-size:13px;font-weight:500;pointer-events:none;opacity:0;transition:opacity .25s;white-space:nowrap;box-shadow:0 2px 12px rgba(0,0,0,.3);"></div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <small class="text-muted">Haritaya tıklayarak veya yukarıya yazarak seçim yapabilirsiniz.</small>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-locate-me"><i class="fas fa-location-arrow"></i> Konumumu Bul</button>
                                    </div>
                                    <input type="hidden" id="pickup_lat" name="pickup_lat" value="{{ old('pickup_lat', $ticket->location?->latitude) }}">
                                    <input type="hidden" id="pickup_lng" name="pickup_lng" value="{{ old('pickup_lng', $ticket->location?->longitude) }}">
                                    <div class="form-group mt-2 mb-0">
                                        <label for="pickup_time_input" class="mb-1"><i class="fas fa-clock text-primary"></i> Tur Saati</label>
                                        <select class="form-control form-control-sm @error('pickup_time') is-invalid @enderror"
                                                id="pickup_time_input" name="pickup_time" disabled>
                                            <option value="">Önce haritadan konum seçin</option>
                                        </select>
                                        <input type="hidden" id="pickup_time_preselect" value="{{ old('pickup_time', optional($ticket->pickup_time)->format('H:i')) }}">
                                        @error('pickup_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Konumun bulunduğu poligonun saatleri listelenir. Opsiyonel.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="room_number"><i class="fas fa-door-open text-secondary"></i> Oda Numarası</label>
                                    <input type="text" class="form-control @error('room_number') is-invalid @enderror"
                                           id="room_number" name="room_number"
                                           value="{{ old('room_number', $ticket->room_number) }}">
                                    @error('room_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="passport_numbers"><i class="fas fa-passport text-primary"></i> Pasaport Numaraları</label>
                            <textarea class="form-control @error('passport_numbers') is-invalid @enderror" 
                                      id="passport_numbers" name="passport_numbers" rows="2"
                                      placeholder="Her yolcu için bir satır">{{ old('passport_numbers', $ticket->passport_numbers) }}</textarea>
                            @error('passport_numbers')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Güncelle
                        </button>
                        <a href="{{ route('agency.tickets.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> İptal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@stop

@push('css')
<link href="https://api.mapbox.com/mapbox-gl-js/v3.4.0/mapbox-gl.css" rel="stylesheet">
<style>
/* GBP - Koyu Mavi */
.badge-gbp { background-color: #1a237e; color: #fff; }
/* RUB - Turuncu */
.badge-rub { background-color: #e65100; color: #fff; }

/* Pickup suggestions dropdown */
.pickup-search-wrap { position: relative; }
.pickup-suggestions { position: absolute; top: 100%; left: 0; right: 0; background: #fff; color: #212529; border: 1px solid #ced4da; border-top: none; border-radius: 0 0 4px 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); z-index: 1050; max-height: 260px; overflow-y: auto; display: none; color-scheme: light; }
.pickup-suggestions.show { display: block; }
.pickup-suggestion { padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f1f3f5; font-size: 13px; }
.pickup-suggestion:last-child { border-bottom: none; }
.pickup-suggestion:hover, .pickup-suggestion.active { background: #f0f7ff; }
.pickup-suggestion-main { font-weight: 600; color: #212529; }
.pickup-suggestion-sub { font-size: 11px; color: #6c757d; margin-top: 2px; }
.pickup-suggestion-empty, .pickup-suggestion-loading { padding: 10px 12px; font-size: 12px; color: #6c757d; font-style: italic; text-align: center; }
</style>
@endpush

@push('js')
<script src="https://api.mapbox.com/mapbox-gl-js/v3.4.0/mapbox-gl.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    var MAPBOX_TOKEN = @json(config('services.mapbox.access_token'));
    var mapEl = document.getElementById('pickup-map');
    if (!mapEl || !MAPBOX_TOKEN) return;
    mapboxgl.accessToken = MAPBOX_TOKEN;
    var inputEl = document.getElementById('pickup_location');
    var latEl = document.getElementById('pickup_lat');
    var lngEl = document.getElementById('pickup_lng');
    var locateBtn = document.getElementById('btn-locate-me');
    var map = new mapboxgl.Map({ container:'pickup-map', style:'mapbox://styles/mapbox/streets-v12', center:[28.27,36.85], zoom:10, language:'tr' });
    map.addControl(new mapboxgl.NavigationControl(),'top-right');
    var marker = null;
    var serviceGeoJson = null;
    var toastEl = document.getElementById('map-toast');
    var toastTimer = null;
    function showMapToast(msg){ if(!toastEl)return; toastEl.textContent=msg; toastEl.style.opacity='1'; clearTimeout(toastTimer); toastTimer=setTimeout(function(){ toastEl.style.opacity='0'; },2200); }

    function setMarker(lat,lng){
        if(!isInsideServiceAreas(lng,lat)){showMapToast('Seçilen konum, servis alanı dışında');return;}
        if(marker){marker.setLngLat([lng,lat]);}else{marker=new mapboxgl.Marker({draggable:true}).setLngLat([lng,lat]).addTo(map);marker.on('dragend',onMarkerDrag);}
        latEl.value=lat;lngEl.value=lng;
        refreshPickupTimeOptions();
    }
    function getPolygonTimesAt(lng,lat){
        if(!serviceGeoJson)return [];
        if(serviceGeoJson.type==='FeatureCollection'){
            var feats=serviceGeoJson.features||[];
            for(var i=0;i<feats.length;i++){
                if(feats[i]&&isInsideGeom(lng,lat,feats[i].geometry)){
                    var t=feats[i].properties&&feats[i].properties.times;
                    return Array.isArray(t)?t.slice().sort():[];
                }
            }
        }
        return [];
    }
    function refreshPickupTimeOptions(){
        var sel=document.getElementById('pickup_time_input');
        if(!sel)return;
        var preEl=document.getElementById('pickup_time_preselect');
        var pre=preEl?preEl.value:'';
        var current=sel.value||pre;
        var lat=parseFloat(latEl.value),lng=parseFloat(lngEl.value);
        sel.innerHTML='';
        function opt(v,t){var o=document.createElement('option');o.value=v;o.textContent=t;return o;}
        if(isNaN(lat)||isNaN(lng)){sel.appendChild(opt('','Önce haritadan konum seçin'));sel.disabled=true;return;}
        var times=getPolygonTimesAt(lng,lat);
        if(!times.length){sel.appendChild(opt('','Bu konumun bulunduğu alanda saat tanımlanmamış'));sel.disabled=true;return;}
        sel.disabled=false;
        sel.appendChild(opt('','-- Saat Seçiniz (opsiyonel) --'));
        times.forEach(function(t){sel.appendChild(opt(t,t));});
        if(current&&times.indexOf(current)!==-1)sel.value=current;
    }
    function onMarkerDrag(){
        var ll=marker.getLngLat();
        if(isInsideServiceAreas(ll.lng,ll.lat)){latEl.value=ll.lat;lngEl.value=ll.lng;reverseGeocode(ll.lng,ll.lat);refreshPickupTimeOptions();}
        else{showMapToast('Seçilen konum, servis alanı dışında');var oLat=parseFloat(latEl.value),oLng=parseFloat(lngEl.value);if(!isNaN(oLat)&&!isNaN(oLng))marker.setLngLat([oLng,oLat]);}
    }
    function reverseGeocode(lng,lat){ fetch('https://api.mapbox.com/geocoding/v5/mapbox.places/'+lng+','+lat+'.json?access_token='+MAPBOX_TOKEN+'&language=tr&limit=1').then(function(r){return r.json();}).then(function(d){if(d.features&&d.features[0]&&inputEl)inputEl.value=d.features[0].place_name;}).catch(function(){}); }
    function forwardGeocode(q,cb){ fetch('https://api.mapbox.com/geocoding/v5/mapbox.places/'+encodeURIComponent(q)+'.json?access_token='+MAPBOX_TOKEN+'&language=tr&limit=1').then(function(r){return r.json();}).then(function(d){if(d.features&&d.features[0]){var c=d.features[0].center;cb(c[0],c[1]);}}).catch(function(){}); }

    function pointInPolygon(lng,lat,coords){var ring=coords[0]||[];var inside=false;for(var i=0,j=ring.length-1;i<ring.length;j=i++){var xi=ring[i][0],yi=ring[i][1],xj=ring[j][0],yj=ring[j][1];if(((yi>lat)!==(yj>lat))&&(lng<(xj-xi)*(lat-yi)/(yj-yi)+xi))inside=!inside;}return inside;}
    function isInsideGeom(lng,lat,g){
        if(!g||!g.type)return false;
        if(g.type==='Polygon')return pointInPolygon(lng,lat,g.coordinates||[]);
        if(g.type==='MultiPolygon'){var cs=g.coordinates||[];for(var i=0;i<cs.length;i++){if(pointInPolygon(lng,lat,cs[i]))return true;}return false;}
        return false;
    }
    function isInsideServiceAreas(lng,lat){
        if(!serviceGeoJson)return true;
        if(serviceGeoJson.type==='FeatureCollection'){
            var feats=serviceGeoJson.features||[];
            if(!feats.length)return true;
            for(var i=0;i<feats.length;i++){if(feats[i]&&isInsideGeom(lng,lat,feats[i].geometry))return true;}
            return false;
        }
        if(serviceGeoJson.type==='Polygon'||serviceGeoJson.type==='MultiPolygon')return isInsideGeom(lng,lat,serviceGeoJson);
        return true;
    }
    function clearServiceLayer(){
        serviceGeoJson=null;
        if(map.getLayer('sa-fill'))map.removeLayer('sa-fill');
        if(map.getLayer('sa-line'))map.removeLayer('sa-line');
        if(map.getSource('sa-src'))map.removeSource('sa-src');
    }
    function drawServiceAreas(geo){
        clearServiceLayer();
        if(!geo)return;
        serviceGeoJson=geo;
        if(!map.isStyleLoaded()){map.on('load',function(){drawServiceAreas(geo);});return;}
        var data=geo.type==='FeatureCollection'?geo:{type:'Feature',properties:{},geometry:geo};
        map.addSource('sa-src',{type:'geojson',data:data});
        map.addLayer({id:'sa-fill',type:'fill',source:'sa-src',paint:{'fill-color':'#0d6efd','fill-opacity':0.12}});
        map.addLayer({id:'sa-line',type:'line',source:'sa-src',paint:{'line-color':'#0d6efd','line-width':2}});
        refreshPickupTimeOptions();
        loadAreaPOIs();
        try{
            var bounds=new mapboxgl.LngLatBounds();
            function addRing(r){(r||[]).forEach(function(p){bounds.extend(p);});}
            function addGeom(g){if(!g)return;if(g.type==='Polygon')addRing((g.coordinates||[])[0]);else if(g.type==='MultiPolygon')(g.coordinates||[]).forEach(function(poly){addRing(poly[0]);});}
            if(geo.type==='FeatureCollection')(geo.features||[]).forEach(function(f){if(f)addGeom(f.geometry);});
            else addGeom(geo);
            if(!bounds.isEmpty())map.fitBounds(bounds,{padding:40,maxZoom:14});
        }catch(e){}
    }

    var tourId = @json($ticket->tour_id);
    if (tourId) {
        var url = (@json(route('agency.tours.details',['tour'=>'__ID__']))).replace('__ID__', tourId);
        fetch(url,{headers:{'Accept':'application/json'}}).then(function(r){return r.json();}).then(function(data){
            if(data&&data.tour&&data.tour.service_areas)drawServiceAreas(data.tour.service_areas);
        }).catch(function(){});
    }

    map.on('click',function(e){
        var lng=e.lngLat.lng,lat=e.lngLat.lat;
        if(isInsideServiceAreas(lng,lat)){setMarker(lat,lng);reverseGeocode(lng,lat);}
        else{showMapToast('Seçilen konum, servis alanı dışında');}
    });
    if(locateBtn&&navigator.geolocation){locateBtn.addEventListener('click',function(){navigator.geolocation.getCurrentPosition(function(pos){var lat=pos.coords.latitude,lng=pos.coords.longitude;map.flyTo({center:[lng,lat],zoom:15});setMarker(lat,lng);reverseGeocode(lng,lat);});});}
    // Anlık arama: yazarken servis alanı içinde kalan sonuçları input altında gösterir
    var suggestionsEl=document.getElementById('pickup-suggestions');
    var searchTimer=null,searchController=null;
    function hideSuggestions(){if(suggestionsEl){suggestionsEl.classList.remove('show');suggestionsEl.innerHTML='';}}
    function buildServiceBounds(){
        if(!serviceGeoJson)return null;
        var b=new mapboxgl.LngLatBounds();
        function addRing(r){(r||[]).forEach(function(p){b.extend(p);});}
        function addGeom(g){if(!g)return;if(g.type==='Polygon')addRing((g.coordinates||[])[0]);else if(g.type==='MultiPolygon')(g.coordinates||[]).forEach(function(p){addRing(p[0]);});}
        if(serviceGeoJson.type==='FeatureCollection')(serviceGeoJson.features||[]).forEach(function(f){if(f)addGeom(f.geometry);});
        else addGeom(serviceGeoJson);
        return b.isEmpty()?null:b;
    }
    function renderSuggestions(feats,strict){
        if(!suggestionsEl)return;
        if(!feats.length){
            var msg=strict?'Servis alanı içinde sonuç bulunamadı. Aramayı genişletin veya haritadan tıklayarak seçin.':'Sonuç bulunamadı.';
            suggestionsEl.innerHTML='<div class="pickup-suggestion-empty">'+msg+'</div>';
            suggestionsEl.classList.add('show');
            return;
        }
        suggestionsEl.innerHTML='';
        if(!strict){var note=document.createElement('div');note.className='pickup-suggestion-empty';note.style.borderBottom='1px solid #f1f3f5';note.style.fontStyle='normal';note.textContent='Bu tur için servis alanı tanımlı değil — sonuçlar filtrelenmedi.';suggestionsEl.appendChild(note);}
        feats.forEach(function(f){
            var item=document.createElement('div');item.className='pickup-suggestion';item.setAttribute('role','option');
            var p=document.createElement('div');p.className='pickup-suggestion-main';p.textContent=f.text||(f.place_name||'').split(',')[0];
            var s=document.createElement('div');s.className='pickup-suggestion-sub';s.textContent=f.place_name||'';
            item.appendChild(p);item.appendChild(s);
            item.addEventListener('mousedown',function(e){e.preventDefault();inputEl.value=f.place_name||'';map.flyTo({center:f.center,zoom:15});setMarker(f.center[1],f.center[0]);hideSuggestions();});
            suggestionsEl.appendChild(item);
        });
        suggestionsEl.classList.add('show');
    }
    function hasServiceAreaPolygons(){
        if(!serviceGeoJson)return false;
        if(serviceGeoJson.type==='FeatureCollection')return (serviceGeoJson.features||[]).length>0;
        return serviceGeoJson.type==='Polygon'||serviceGeoJson.type==='MultiPolygon';
    }
    // ===== Mapbox Tilequery POI cache =====
    var cachedPOIs=[];
    function normalizeText(s){return (s==null?'':String(s)).toLowerCase().replace(/[\u0130\u0131]/g,'i').replace(/[\u015e\u015f]/g,'s').replace(/[\u011e\u011f]/g,'g').replace(/[\u00dc\u00fc]/g,'u').replace(/[\u00d6\u00f6]/g,'o').replace(/[\u00c7\u00e7]/g,'c').trim();}
    function loadAreaPOIs(){
        var b=buildServiceBounds();
        if(!b){cachedPOIs=[];return;}
        var c=b.getCenter();
        var sw=b.getSouthWest(),ne=b.getNorthEast();
        var dx=ne.lng-sw.lng,dy=ne.lat-sw.lat;
        var radiusMeters=Math.min(50000,Math.max(3000,Math.round(Math.sqrt(dx*dx+dy*dy)*111000*0.7)));
        var url='https://api.mapbox.com/v4/mapbox.mapbox-streets-v8/tilequery/'+c.lng+','+c.lat+'.json?radius='+radiusMeters+'&limit=50&dedupe=true&geometry=point&layers=poi_label&access_token='+encodeURIComponent(MAPBOX_TOKEN);
        fetch(url).then(function(r){return r.json();}).then(function(data){
            var feats=(data&&data.features)||[];
            cachedPOIs=feats.filter(function(f){
                if(!f.properties||!f.properties.name)return false;
                var c=f.geometry&&f.geometry.coordinates;
                if(!Array.isArray(c))return false;
                return isInsideServiceAreas(c[0],c[1]);
            });
        }).catch(function(){cachedPOIs=[];});
    }
    function searchCachedPOIs(query){
        var qNorm=normalizeText(query);
        if(!qNorm||qNorm.length<2||!cachedPOIs.length)return [];
        return cachedPOIs.filter(function(p){return normalizeText(p.properties.name).indexOf(qNorm)!==-1;}).slice(0,8).map(function(p){
            var cat=p.properties.category_en||p.properties.maki||p.properties.class||'';
            return {place_name:p.properties.name+(cat?' — '+cat:''),text:p.properties.name,center:p.geometry.coordinates,_isPoi:true,_cat:cat};
        });
    }
    function instantGeocode(query){
        if(!query||query.length<2){hideSuggestions();return;}
        var poiResults=searchCachedPOIs(query);
        if(suggestionsEl){
            if(poiResults.length){renderSuggestions(poiResults,hasServiceAreaPolygons());}
            else{suggestionsEl.innerHTML='<div class="pickup-suggestion-loading">Aranıyor...</div>';suggestionsEl.classList.add('show');}
        }
        if(searchController){try{searchController.abort();}catch(e){}}
        searchController=(typeof AbortController!=='undefined')?new AbortController():null;
        var params='access_token='+MAPBOX_TOKEN+'&language=tr&limit=10&types=poi,address,neighborhood';
        var b=buildServiceBounds();
        if(b){params+='&bbox='+b.getWest()+','+b.getSouth()+','+b.getEast()+','+b.getNorth();var c=b.getCenter();params+='&proximity='+c.lng+','+c.lat;}
        var url='https://api.mapbox.com/geocoding/v5/mapbox.places/'+encodeURIComponent(query)+'.json?'+params;
        fetch(url,searchController?{signal:searchController.signal}:{}).then(function(r){return r.json();}).then(function(data){
            var feats=(data&&data.features)||[];
            var strict=hasServiceAreaPolygons();
            var geocodeFiltered=strict?feats.filter(function(f){var c=f.center;return Array.isArray(c)&&isInsideServiceAreas(c[0],c[1]);}):feats;
            var seen={},combined=[];
            poiResults.concat(geocodeFiltered).forEach(function(f){
                if(!f.center)return;
                var key=Math.round(f.center[0]*10000)+','+Math.round(f.center[1]*10000);
                if(seen[key])return;
                seen[key]=true;
                combined.push(f);
            });
            renderSuggestions(combined,strict);
        }).catch(function(){if(poiResults.length)renderSuggestions(poiResults,hasServiceAreaPolygons());});
    }
    if(inputEl){
        inputEl.addEventListener('input',function(){clearTimeout(searchTimer);var q=inputEl.value.trim();searchTimer=setTimeout(function(){instantGeocode(q);},250);});
        inputEl.addEventListener('focus',function(){var q=inputEl.value.trim();if(q.length>=2)instantGeocode(q);});
        inputEl.addEventListener('blur',function(){setTimeout(hideSuggestions,180);});
        inputEl.addEventListener('keydown',function(e){if(e.key==='Escape')hideSuggestions();});
    }
    var oldLat=parseFloat(latEl.value||''),oldLng=parseFloat(lngEl.value||'');
    if(!isNaN(oldLat)&&!isNaN(oldLng)){map.flyTo({center:[oldLng,oldLat],zoom:15});setMarker(oldLat,oldLng);}
    else if(inputEl&&inputEl.value){forwardGeocode(inputEl.value,function(lng,lat){map.flyTo({center:[lng,lat],zoom:15});setMarker(lat,lng);});}
});
</script>
@endpush

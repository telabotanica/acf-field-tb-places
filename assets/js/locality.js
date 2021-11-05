const NOMINATIM_OSM_URL = 'https://nominatim.openstreetmap.org/search';
const NOMINATIM_OSM_DEFAULT_PARAMS = {
    'format': 'json',
    'addressdetails': 1,
    'limit': 10
};
const FR_DATA_GOUV_URL = 'https://api-adresse.data.gouv.fr/reverse/';
const ESC_KEY_STRING = /^Esc(ape)?/;

function TbPlaces($fieldContainer) {
    this.searchResults = [];
    this.fieldContainer = $fieldContainer;
}

TbPlaces.prototype.init = function() {
    this.initForm();
    this.initEvts();
};

TbPlaces.prototype.initForm = function() {
    this.places = $('.tb-places', this.fieldContainer);
    this.hiddenField = $('input[type=hidden]', this.fieldContainer);
    this.placeType = this.hiddenField.data('tb-type') || 'all';
    this.placeCountries = this.hiddenField.data('tb-countries');
    this.placeLabel = $('label', this.fieldContainer);
    this.placesResultsContainer = $('.tb-places-results-container', this.fieldContainer);
    this.placesResults = $('.tb-places-results', this.placesResultsContainer);
    this.placesCloseButton = $('.tb-places-close', this.fieldContainer);
};

TbPlaces.prototype.initEvts = function() {
    if (0 < this.places.length) {
        let data = {value: ''};

        try {
          data = JSON.parse(this.hiddenField.val());
        } catch (e) {
          console.warn(e);
        }
        this.places.val(data.value);
        this.toggleCloseButton(false);
        this.places.off('input').on('input', debounce(this.launchSearch.bind(this), 500));
        this.places.off('keydown').on('keydown', debounce(this.handlePlacesKeydown.bind(this), 500));
    }
};

TbPlaces.prototype.handlePlacesKeydown = function(evt) {
    const suggestionEl = $('.tb-places-suggestion', this.placesResults),
        isEscape = 27 === evt.keyCode || ESC_KEY_STRING.test(evt.key),
        isArrowDown = 40 === evt.keyCode || 'ArrowDown' === evt.key,
        isEnter = 13 === evt.keyCode || 'Enter' === evt.key;

    if (isEscape || isArrowDown || isEnter) {
        evt.preventDefault();

        if (isEscape) {
            this.placesCloseButton.trigger('click');
            this.places.focus();
        } else if(isArrowDown || isEnter) {
            if ( 0 <  suggestionEl.length) {
                suggestionEl.first().focus();
            } else {
                this.launchSearch();
            }
        }
    }
};

TbPlaces.prototype.launchSearch = function () {
    if (!!this.places.val()) {
        const url = NOMINATIM_OSM_URL,
            params = {
                'q': this.places.val(),
                'format': 'json',
                'polygon_geojson': 1,
                'zoom': 17
            };

        if(!!this.placeCountries && '' !== this.placeCountries) {
            params.countrycodes = this.placeCountries;
        }

        this.placeLabel.addClass('loading');
        $.ajax({
            method: "GET",
            url: url,
            data: {...NOMINATIM_OSM_DEFAULT_PARAMS, ...params},
            success: this.nominatimOsmResponseCallback.bind(this),
            error: () => {
                this.placeLabel.removeClass('loading');
                this.handleSearchError();
            }
        });
    }
};

TbPlaces.prototype.nominatimOsmResponseCallback = function(data) {
    const lthis = this;
    this.placeLabel.removeClass('loading');
    if (0 < data.length) {
        $('#tb-places-error', this.fieldContainer).remove();
        this.searchResults = this.filterSuggestions(data);
        this.setSuggestions();
        this.toggleCloseButton();
        this.resetOnClick();
        this.onSuggestionSelected();
    } else {
        this.handleSearchError();
    }
};

TbPlaces.prototype.filterSuggestions = function(data) {
    const lthis = this,
        filteredSuggestions = [];

    data.forEach(suggestion => {
        if(!lthis.validateSuggestionData(suggestion)) {
            return;
        }

        const locality = lthis.getLocalityFromData(suggestion.address),
            suggestionPlaceType = lthis.getSuggestionPlaceType(locality.type, suggestion.address);

        if('all' !== lthis.placeType && lthis.placeType !== suggestionPlaceType) {
            return;
        }

        suggestion.locality = locality;
        suggestion.type = suggestionPlaceType;
        filteredSuggestions.push(suggestion);
    });

    return filteredSuggestions;
};

TbPlaces.prototype.validateSuggestionData = function(suggestion) {
    const validCoordinates = undefined !== suggestion.lat && undefined !== suggestion.lon,
        validAddressData = undefined !== suggestion.address,
        validDisplayName = undefined !== suggestion['display_name'];
    let validCountryData = validAddressData && undefined !== suggestion.address.country && undefined !== suggestion.address['country_code'];

    if(!!this.placeCountries && '' !== this.placeCountries && validCountryData) {
        validCountries = this.placeCountries.toLowerCase().split(',');
        validCountryData &= validCountries.includes(suggestion.address['country_code']);
    }

    return (validCoordinates && validDisplayName && validCountryData);
};

TbPlaces.prototype.getLocalityFromData = function(addressData) {
    const locationNameType = ['village', 'city', 'locality', 'municipality', 'county', 'state'].find(locationNameType => addressData[locationNameType] !== undefined),
        locality = {type:'unknown',name:''};//default

    if (!!locationNameType) {
        locality.type = locationNameType;
        locality.name = addressData[locationNameType];
    }
    return locality;
};

TbPlaces.prototype.getSuggestionPlaceType = function(localityType, addressData) {
    let placeType = 'address';//default

    if('unknown' === localityType) {
        placeType = 'country';
    } else if((undefined === addressData.road || '' === addressData.road) && (undefined === addressData.natural || '' === addressData.natural)) {
        placeType = ['village', 'city', 'locality'].includes(localityType) ? 'city': 'townhall';
    }
    return placeType
};

TbPlaces.prototype.setSuggestions = function() {
    const lthis = this,
        acceptedSuggestions = [];

    this.placesResults.empty();
    this.searchResults.forEach(suggestion => {
        const place = suggestion['display_name'];

        if (place && !acceptedSuggestions.includes(place)) {//deduplicate on place names
            acceptedSuggestions.push(place);
            lthis.placesResults.append(
                '<li class="tb-places-suggestion" data-place-id="'+suggestion['place_id']+'" tabindex="-1">' +
                    place +
                '</li>'
            );
        }
    });
    if(0 < acceptedSuggestions.length) {
        this.placesResultsContainer.removeClass('tb-hidden');
    } else {
        this.resetPlacesSearch();
    }
};

TbPlaces.prototype.onSuggestionSelected = function() {
    const lthis = this,
     searchResults = this.searchResults;

    $('.tb-places-suggestion', this.placesResults).off('click').on('click', function (evt) {
        const $thisSuggestion = $(this),
            suggestion = lthis.searchResults.find(suggestion => suggestion['place_id'] === $thisSuggestion.data('placeId'));

        evt.preventDefault();
        lthis.places.val($thisSuggestion.text());
        lthis.setFormatedLocalityData(suggestion);
        lthis.placesCloseButton.trigger('click');

    }).off('keydown').on('keydown', function (evt) {
        evt.preventDefault();

        const $thisSuggestion = $(this);

        if (13 === evt.keyCode || 'Enter' === evt.key) {
            $thisSuggestion.trigger('click');
        } else if (38 === evt.keyCode || 'ArrowUp'=== evt.key) {
            if(0 < $thisSuggestion.prev().length) {
                $thisSuggestion.prev().focus();
            } else {
                lthis.places.focus();
            }
        } else if((40 === evt.keyCode || 'ArrowDown' === evt.key) && 0 < $thisSuggestion.next().length) {
            $thisSuggestion.next().focus();
        } else if (27 === evt.keyCode || ESC_KEY_STRING.test(evt.key)) {
            lthis.placesCloseButton.trigger('click');
            lthis.places.focus();
        }
    });
};

TbPlaces.prototype.setFormatedLocalityData = function(suggestion) {
    const suggestionPlaceType = suggestion.type,
        addressData = suggestion.address,
        coordinates = this.formatCoordinates({
            lat : suggestion.lat,
            lng : suggestion.lon
        }),
        countryCode = addressData['country_code'];

    if('country' !== suggestionPlaceType && 'fr' === countryCode && !addressData.postcode) {
        this.getLocalityPostCode(suggestion, coordinates);
    } else {
        let place = {
                type: suggestionPlaceType,
                countryCode: countryCode,
                latlng: coordinates,
                value: suggestion['display_name']
            },
            placeName;

        this.placeLabel.removeClass('loading');

        if('country' === suggestionPlaceType) {
            placeName = addressData.country;
        } else {
            if('address' === suggestionPlaceType) {
                if(undefined !== addressData.road) {
                    placeName = addressData['house_number'] ?? '';
                    placeName += ' '+addressData.road;
                } else if (undefined !== addressData.natural) {
                    placeName = addressData.natural;
                }
                place.city = suggestion.locality.name;
            } else {
                placeName = suggestion.locality.name;
            }

            place = {
                ...place,
                country: addressData.country,
                administrative: addressData.state,
                postcode: addressData.postcode
            };
        }

        if(!!placeName && '' !== placeName) {
            place.name = placeName;
            console.log(place);
            // set tb-places hidden field value
            this.hiddenField.val(JSON.stringify(place));
        }
    }
};

TbPlaces.prototype.formatCoordinates = function (coordinates) {
    coordinates.lat = Number.parseFloat(coordinates.lat);
    coordinates.lng = Number.parseFloat(coordinates.lng);

    if(Number.isNaN(coordinates.lat) || Number.isNaN(coordinates.lng)) {
        return null;
    }

    return coordinates;
};

TbPlaces.prototype.getLocalityPostCode = function (suggestion, coordinates) {
    const lthis = this;

    this.placeLabel.addClass('loading');

    $.ajax({
        method: "GET",
        url: FR_DATA_GOUV_URL,
        data: coordinates,
        success: function(data) {
            lthis.placeLabel.removeClass('loading');
            if ('undefined' !== data && 0 < data.features.length) {
                suggestion.address.postcode = data.features[0]['properties']['postcode'];
                lthis.setFormatedLocalityData(suggestion);
                lthis.toggleCloseButton();
                lthis.resetOnClick();
            } else {
                lthis.handleSearchError();
            }
        },
        error: () => {
            this.placeLabel.removeClass('loading');
            this.handleSearchError();
        }
    });
};

TbPlaces.prototype.resetOnClick = function () {
    const lthis = this;

    this.placesCloseButton.off('click').on('click', function (event) {
        event.preventDefault();
        lthis.resetPlacesSearch();
    });
};

TbPlaces.prototype.toggleCloseButton = function(isShow = true) {
    this.placesCloseButton.toggleClass('tb-hidden', !isShow);
    $('.tb-places-search-icon', this.fieldContainer).toggleClass('tb-hidden', isShow);
};

TbPlaces.prototype.handleSearchError = function() {
    this.resetPlacesSearch();
    if (0 === $('#tb-places-error', this.fieldContainer).length) {
        this.places.closest('.input-search-container').after(
            `<span id="tb-places-error" class="error mb-3 mt-3">
                Votre recherche n’a pas donné de résultat pour le moment.<br>Vous pouvez soit poursuivre ou modifier votre recherche,<br>soit rechercher votre station directement sur la carte.
            </span>`
        );
        setTimeout(function() {
            $('#tb-places-error', this.fieldContainer).remove();
        }, 10000);
    }
};

TbPlaces.prototype.resetPlacesSearch = function() {
    this.toggleCloseButton(false);
    this.placeLabel.removeClass('loading');
    this.placesResultsContainer.addClass('tb-hidden');
    this.placesResults.empty();
};

function debounce (callback, delay) {
    let timer;

    return function() {
        const args = arguments,
            context = this;
        clearTimeout(timer);
        timer = setTimeout(function() {
            callback.apply(context, args);
        }, delay)
    }
}
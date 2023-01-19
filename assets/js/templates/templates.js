export const mapTemplates = {
    listingMarkerPopup: ({
        images,
        financials,
        address,
        metrics,
        mlsNumber,
        listingUrl,
        feedId
    }) => `
        <div class="marker-popup-inner">
            <div class="marker-popup-inner__left-col">
                <a class="marker-popup-inner__img-wrap mb3" href="/${listingUrl}">
                    ${images && images[0] 
                        ? `<img src=${images[0]} alt="#" class="marker-popup-inner__img of"/>` 
                        : `<div class="marker-popup-inner__img of default-img-bg"></div>`
                    }
                </a>
                
                ${financials && financials.listingPrice ?
                    `<span class="tiny-text_bold">${_formatCurrencyCa(financials.listingPrice)}</span>`
                : ''}   
            </div>
            
            <div class="marker-popup-inner__right-col">
                ${(address && address.streetAddress && address.city) ?
                    `<a class="marker-popup-inner__title small-text link-dark h5 mb3" href="/${listingUrl}">${address.streetAddress + ', ' + address.city}</a>`
                : ''}
                
                ${metrics ? `
                    <div class="mb3 extra-small-text">
                        ${mapTemplates.metrics(metrics)}
                    </div>
                ` : ''}
                
                ${mlsNumber ?
                    `<span class="gray-mls-after ${feedId == 'ddf' ? 'icon-mls-min' : ''} tiny-text">${mlsNumber}</span>`
                : ''}
            </div>
        </div>
    `,

    estateCard: ({
         images,
         isNew,
         forSaleByOwner,
         listingId,
         userFavorite,
         financials,
         address,
         metrics,
         mlsNumber,
         listingUrl,
         feedId
    }) =>{
        const sliderParams = {
            lazyLoad: 'ondemand',
            dots: true,
            responsive: [
                {
                    breakpoint: 1000,
                    settings: {
                        arrows: false,
                    }
                }
            ]
        };

        return `
            <a class="estate-card _small _transparent-controls js-estate-card" href="/${listingUrl}">
                <div class="estate-card__slider-wrap js-wrap">
                    <div
                        class="estate-cards-slider js-estate-card-slider"
                        data-lazy-inner="true"
                        data-img-selector="ec-src"
                        data-slider-parameters=${JSON.stringify(sliderParams)}
                    >
                        ${mapTemplates.estateSliderItems(images)}
                    </div>
                    
                    <div class="estate-card__header">
                        <div class="estate-card__labels-wrap">
                            ${isNew ? `<span class="estate-card__label schild_2">NEW</span>` : ''}
                            ${forSaleByOwner ? `<span class="estate-card__label schild_2">for sale by owner</span>` : ''}
                        </div>
            
                        <span
                            class="estate-card__add-to-favorite circle-button _ic-fs-12 favorite-toggle js-call-popup js-prevent ${userFavorite ? '_active' : '' }"
                            data-callback="toggle_active"
                            data-url="${ADD_TO_FAVORITES_PATH.replace('@', listingId)}"
                        ></span>
                    </div>
                    
                    <div class="estate-card__arrows-wrap js-slider-nav">
                        <span class="estate-card__arrow circle-button _bordered icon-angle-left js-arrow-left js-prevent"></span>
                        <span class="estate-card__arrow circle-button _bordered icon-angle-right js-arrow-right js-prevent"></span>
                    </div>
                </div>
                
                <div class="estate-card__description pt10 pb20">
                    ${financials && financials.listingPrice ? 
                        `<span class="estate-card__title body-text_bold mb5">${_formatCurrencyCa(financials.listingPrice)}</span>`
                    : ''}
                  
                    ${(address && address.streetAddress && address.city) ? 
                        `<span class="estate-card__location h6 mb5">${address.streetAddress + ', ' + address.city}</span>` 
                    : ''}
                    
                    ${metrics ? `
                        <div class="estate-card__metrics-wrap mb10">
                            ${mapTemplates.metrics(metrics)}
                        </div>
                    ` : ''}
                    
                    ${mlsNumber ? 
                        `<span class="gray-mls-after ${feedId == 'ddf' ? 'icon-mls-min' : ''} tiny-text">${'MLS® ' + mlsNumber}</span>`
                    : ''}
                </div>
            </a>
        `
    },

    estateSliderItems: (imagesArray) => {
        if (imagesArray && imagesArray.length) {
            return imagesArray.map(img => `
                <div class="estate-cards-slider__item">
                    <img class="estate-cards-slider__img of" data-ec-src=${img} src='data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==' alt="#" />
                </div>
            `).join('')
        } else {
            return `<div class="estate-cards-slider__item default-img-bg"></div>`
        }
    },

    metrics: (metrics) => {
        const
            metricsLabels = {
                bedRooms: {
                    suffix: 'Beds',
                },
                bathRooms: {
                    suffix: 'Baths',
                },
                stories: {
                    suffix: 'Stories',
                },
                lotSize: {
                    prefix: 'lot',
                    suffix: metrics.lotSizeUnits || 'sqft',
                },
                sqrtFootage: {
                    suffix: metrics.sqrtFootageUnits || 'sqft',
                },
                yearBuilt: {
                    suffix: 'built',
                }
            },
            outputProps = ['bedRooms','bathRooms','stories','lotSize','sqrtFootage','yearBuilt'];

        let metricsItems = [];

        for (let i = 0; i < outputProps.length; i++) {
            const val = metrics[outputProps[i]];

            if (val) {
                const
                    label = metricsLabels[outputProps[i]],
                    prefix = label && label.prefix,
                    suffix = label && label.suffix;

                metricsItems.push(`
                    <div class="metrics__item">
                        ${prefix ? `<span class="metrics__label extra-small-text">${prefix}</span>` : ''}
                        <span class="metrics__val small-text_bold extra-small-text">${val}</span>
                        ${suffix ? `<span class="metrics__label small-text extra-small-text">${suffix}</span>` : ''}
                    </div>
                `);
            }
        }

        return `
            <div class="metrics _simple">
                ${metricsItems.join('')}
            </div>
        `
    },

    yelpMarkerPopup: ({
        name,
        rating,
        review_count,
        image_url,
        categories,
        url,
    }) => `
        <div class="yelp-marker-popup-inner">
            <a class="yelp-marker-popup-inner__img-wrap ${!image_url && 'default-img-bg-small'}" href="${url}" rel="nofollow" target="_blank">
                ${image_url ? `<img src=${image_url} alt="#" class="of"/>` : ''}
            </a>
            
            <div class="yelp-marker-popup-inner__info">
                <div class="yelp-marker-popup-inner__description">
                    <a class="yelp-marker-popup-inner__title link-dark h6" href="${url}" rel="nofollow" target="_blank">${name}</a>
                    <span class="yelp-marker-popup-inner__categories tiny-text">${categories.map(item => item.title).join(', ')}</span>
                </div>
                
                <div class="yelp-marker-popup-inner__rating-wrap">
                    <div class="rating _${rating.toString().replace('.','')}"></div>
                    
                    <div class="yelp-marker-popup-inner__reviews very-tiny">
                        <span>${review_count}</span>
                        <span>&nbsp;Reviews</span>
                    </div>
                    
                    <a class="yelp-logo" href="${url}" rel="nofollow" target="_blank"></a>
                </div>
            </div>
        </div>
    `,

    yelpSimpleCard: (data) => `
        <div class="yelp-simple-card">
            ${mapTemplates.yelpMarkerPopup(data)}
        </div>
    `,

    schoolCard: ({
        name,
        street,
        city,
        state,
        distance,
        rating,
        rank='Above Average',
    }) => {
        rating = rating && rating !== 'Rating 7/10' ? `Rating ${rating}/10` : 'No Rating';
        return `
        <div class="school-card">
            <span class="school-card__title h5">${name}</span>
            <span class="school-card__address tiny-text">
                ${[street, city, state].filter(el => el !== null).join(', ')}
            </span>
            
            <div class="school-card__bottom mt5">
                <span class="tiny-text">
                    ${[rating].filter(el => el !== null && el !== '').join(' | ')}
                </span>    
            </div>
        </div>
    `},

    busStopCard: ({name}) => {
      return `
        <div class="bus-card">
            <span class="bus-card__title h6">${name}</span>
        </div>
    `},

    // TODO school rating
    // <span className="school-card__separate tiny-text">
    //     ${[distance, ('Rating ' + rating + '/10')].filter(el => el !== null).join(' | ')}
    // </span>
    yelpCard: ({
        name,
        image_url,
        categories,
        url,
    }) => `
        <div class="yelp-card">
            <a class="yelp-card__img-wrap yelp-link mb10 ${!image_url ? 'default-img-bg-small' : ''}" href="#" data-url="${url}" rel="nofollow">
                ${image_url ? `<img src=${image_url} alt="#" class="of"/>` : ''}
            </a>
            
            <a class="yelp-card__title link-dark h6 mb5 yelp-link" href="#" data-url="${url}" rel="nofollow">${name}</a>
            <span class="yelp-card__categories mb5 tiny-text">${categories.map(item => item.title).join(', ')}</span>
            <a class="yelp-logo yelp-link" href="#" data-url="${url}" rel="nofollow"></a>
        </div>
    `,

    routeCard: ({ address, destination, time, error }) => {
        const
            { label } = address,
            parsedTime =
                time ? (
                time >= 86400 ? `${Math.ceil((time / 86400) * 10)  / 10} days` :
                time >= 3600 ? `${Math.ceil((time / 3600) * 10) / 10} hours` :
                time >= 60 ? `${Math.ceil(time / 60)} minutes` :
                `${time} seconds` ) : false;

        return `
            <div class="route-card">
                <div class="route-card__description">
                    ${error ? `
                        <span class="route-card__error h5 mb5">${error}</span>
                    ` : `
                        <span class="route-card__time h5 mb5">${parsedTime}</span>
                    `
                    }
                    
                    <span class="route-card__address tiny-text">${label}</span>         
                </div>

                <div class="route-card__remove icon-cross js-remove-route" data-destination="${destination}"></div>
            </div>
        `
    }
};

export const autofillTemplates = {
    optionsList: (data) => {
        const { array, noResult } = data;

        return (`
            ${array.map(({ code, dial_code, name }) => `
                <span
                    class="autofill-option js-autofill-option"
                    data-value="${code} ${dial_code}"
                    data-name="${name}"
                >
                    ${code} ${dial_code}
                </span>
            `).join('')}
            
            ${autofillTemplates.noResult(noResult)}
        `)
    },

    noResult: (data) => {
        const { text='', mod='', textMod='small-text' } = data;

        return (`
            <span class="autofill-no-results js-autofill-no-results ${textMod} ${mod}">${text}</span>
        `)
    },

    option: (props) => {
        const { text, url } = props;

        return url ? `
            <a class="autofill-option" href="${url}">${text}</a>
        ` : `
            <span class="autofill-option">${text}</span>
        `
    },

    category: (props) => {
        const { title, options } = props;

        return `
            <div class="options-category">
                <div class="options-category__title">${title}</div>
                <div class="options-category__list">${options}</div>
            </div>
        `
    },

    optionsCategories: (obj) => {
        const categories = [];

        Object.keys(obj).forEach(item => {
            categories.push(autofillTemplates.category({
                title: item,
                options: obj[item].join(''),
            }))
        })

        return categories;
    }
};
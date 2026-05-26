import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, FormTokenField, Spinner, Notice } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { useMemo, useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

const SLIDER_THRESHOLD = 6;
const SKELETON_COUNT   = 8;

export default function Edit( { attributes, setAttributes } ) {
	const { selectedProducts, orderby, order } = attributes;

	const blockProps = useBlockProps( { className: 'products-block-section' } );

	// Light fetch for FormTokenField suggestions
	const allProducts = useSelect(
		( select ) =>
			select( coreStore ).getEntityRecords( 'postType', 'product', {
				per_page: 100,
				status:   'publish',
				_fields:  'id,title',
			} ),
		[]
	);

	// Full fetch with embedded media for preview cards
	const [ previewProducts, setPreviewProducts ] = useState( null );
	const selectedKey = selectedProducts.join( ',' );

	useEffect( () => {
		if ( ! selectedProducts.length ) {
			setPreviewProducts( null );
			return;
		}
		setPreviewProducts( null );
		let cancelled = false;
		apiFetch( {
			path: addQueryArgs( '/wp/v2/product', {
				include:  selectedProducts,
				per_page: selectedProducts.length,
				_embed:   1,
			} ),
		} )
			.then( ( products ) => { if ( ! cancelled ) setPreviewProducts( products ); } )
			.catch( () => { if ( ! cancelled ) setPreviewProducts( [] ); } );
		return () => { cancelled = true; };
	}, [ selectedKey ] ); // eslint-disable-line react-hooks/exhaustive-deps

	const productsByTitle = useMemo( () => {
		if ( ! allProducts ) return {};
		return Object.fromEntries(
			allProducts.map( ( p ) => [ p.title.rendered, p.id ] )
		);
	}, [ allProducts ] );

	const suggestions    = useMemo( () => Object.keys( productsByTitle ), [ productsByTitle ] );
	const selectedTitles = useMemo( () => {
		if ( ! allProducts ) return [];
		return selectedProducts
			.map( ( id ) => allProducts.find( ( p ) => p.id === id )?.title.rendered )
			.filter( Boolean );
	}, [ allProducts, selectedProducts ] );

	function onProductsChange( titles ) {
		setAttributes( {
			selectedProducts: titles.map( ( t ) => productsByTitle[ t ] ).filter( Boolean ),
		} );
	}

	const useSlider     = selectedProducts.length > SLIDER_THRESHOLD;
	const showRealCards = previewProducts && previewProducts.length > 0;

	return (
		<>
			<InspectorControls>
				<PanelBody title="Odabir proizvoda">
					{ ! allProducts ? (
						<Spinner />
					) : (
						<FormTokenField
							label="Proizvodi"
							value={ selectedTitles }
							suggestions={ suggestions }
							onChange={ onProductsChange }
							placeholder="Pretraži i dodaj proizvod…"
						/>
					) }
				</PanelBody>
				<PanelBody title="Sortiranje (bez odabira)" initialOpen={ false }>
					<SelectControl
						label="Sortiranje"
						value={ orderby }
						options={ [
							{ label: 'Datum',        value: 'date' },
							{ label: 'Popularnost',  value: 'popularity' },
							{ label: 'Ocena',        value: 'rating' },
							{ label: 'Cena',         value: 'price' },
							{ label: 'Naziv',        value: 'title' },
						] }
						onChange={ ( v ) => setAttributes( { orderby: v } ) }
					/>
					<SelectControl
						label="Redosled"
						value={ order }
						options={ [
							{ label: 'Opadajući', value: 'DESC' },
							{ label: 'Rastuci',   value: 'ASC' },
						] }
						onChange={ ( v ) => setAttributes( { order: v } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="container">
					{ useSlider && (
						<Notice status="info" isDismissible={ false }>
							{ selectedProducts.length } proizvoda — prikazuje se kao slider na sajtu
						</Notice>
					) }

					{ showRealCards ? (
						<ul className="products-grid">
							{ previewProducts.map( ( product ) => {
								const media  = product?._embedded?.[ 'wp:featuredmedia' ]?.[ 0 ];
								const imgUrl = media?.media_details?.sizes?.[ 'amelia-product' ]?.source_url
									|| media?.source_url
									|| '';
								return (
									<li key={ product.id } className="product">
										<div className="product-image-wrap">
											{ imgUrl && (
												<img src={ imgUrl } alt={ product.title.rendered } />
											) }
										</div>
										<div className="product-info">
											<h3 className="woocommerce-loop-product__title">
												{ product.title.rendered }
											</h3>
											<span className="price">—</span>
											<span className="button">Dodaj u korpu</span>
										</div>
									</li>
								);
							} ) }
						</ul>
					) : (
						<ul className="products-grid">
							{ Array( SKELETON_COUNT ).fill( null ).map( ( _, i ) => (
								<li key={ i } className="product">
									<div className="product-image-wrap" />
									<div className="product-info">
										<div className="product-preview-title" />
										<div className="product-preview-price" />
									</div>
								</li>
							) ) }
						</ul>
					) }
				</div>
			</section>
		</>
	);
}

import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, SelectControl, ToggleControl, Spinner } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { useMemo } from '@wordpress/element';

export default function Edit( { attributes, setAttributes } ) {
	const { count, columns, orderby, hideEmpty } = attributes;

	const blockProps = useBlockProps( { className: 'category-grid-section' } );

	const rawCats = useSelect(
		( select ) =>
			select( coreStore ).getEntityRecords( 'taxonomy', 'product_cat', {
				per_page: 100,
				hide_empty: hideEmpty,
			} ),
		[ hideEmpty ]
	);

	const categories = useMemo( () => {
		if ( ! rawCats ) return null;

		let cats = rawCats.filter( ( c ) => c.slug !== 'uncategorized' );

		if ( orderby === 'name' ) {
			cats = [ ...cats ].sort( ( a, b ) => a.name.localeCompare( b.name ) );
		} else if ( orderby === 'count' ) {
			cats = [ ...cats ].sort( ( a, b ) => b.count - a.count );
		}

		return cats.slice( 0, count );
	}, [ rawCats, count, orderby ] );

	if ( ! categories ) {
		return (
			<section { ...blockProps }>
				<div className="categories-grid container" style={ { justifyContent: 'center', padding: '3rem 0' } }>
					<Spinner />
				</div>
			</section>
		);
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title="Podešavanja kategorija">
					<RangeControl
						label="Broj kategorija"
						value={ count }
						onChange={ ( v ) => setAttributes( { count: v } ) }
						min={ 2 }
						max={ 12 }
					/>
					<RangeControl
						label="Broj kolona"
						value={ columns }
						onChange={ ( v ) => setAttributes( { columns: v } ) }
						min={ 1 }
						max={ 6 }
					/>
					<SelectControl
						label="Sortiranje"
						value={ orderby }
						options={ [
							{ label: 'Naziv (A–Z)',    value: 'name' },
							{ label: 'Broj proizvoda', value: 'count' },
							{ label: 'Redosled',       value: 'menu_order' },
						] }
						onChange={ ( v ) => setAttributes( { orderby: v } ) }
					/>
					<ToggleControl
						label="Sakrij prazne kategorije"
						checked={ hideEmpty }
						onChange={ ( v ) => setAttributes( { hideEmpty: v } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div
					className="categories-grid container"
					style={ { '--columns': columns } }
				>
					{ categories.map( ( cat ) => (
						<div key={ cat.id } className="category-card">
							<div className="category-card-overlay">
								<h3>{ cat.name }</h3>
								<span>{ cat.count } { cat.count === 1 ? 'proizvod' : 'proizvoda' }</span>
							</div>
						</div>
					) ) }
				</div>
			</section>
		</>
	);
}

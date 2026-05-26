import {
	useBlockProps,
	InspectorControls,
	RichText,
} from '@wordpress/block-editor';
import {
	PanelBody,
	RadioControl,
	FontSizePicker,
	SelectControl,
	TextareaControl,
} from '@wordpress/components';

const FONT_SIZES = [
	{ name: 'Mali',         slug: 'small',  size: '0.875rem' },
	{ name: 'Normalni',     slug: 'normal', size: '1rem' },
	{ name: 'Srednji',      slug: 'medium', size: '1.25rem' },
	{ name: 'Veliki',       slug: 'large',  size: '1.75rem' },
	{ name: 'Veoma veliki', slug: 'xl',     size: '2.5rem' },
	{ name: 'Ogroman',      slug: 'huge',   size: '3.5rem' },
];

const ALIGN_OPTIONS = [
	{ label: 'Levo',   value: 'left' },
	{ label: 'Centar', value: 'center' },
	{ label: 'Desno',  value: 'right' },
];

// Extract the src URL from a pasted <iframe> tag or return the input as-is.
function getMapSrc( embed ) {
	if ( ! embed ) return '';
	const match = embed.match( /src=["']([^"']+)["']/ );
	return match ? match[ 1 ] : embed;
}

export default function Edit( { attributes, setAttributes } ) {
	const {
		reversed,
		title, workingHours, contactInfo,
		mapEmbed,
		titleFontSize, contentFontSize,
		titleAlign, contentAlign,
	} = attributes;

	const mapSrc = getMapSrc( mapEmbed );

	const blockProps = useBlockProps( {
		className: `contact-section${ reversed ? '' : ' is-reversed' }`,
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title="Raspored">
					<RadioControl
						selected={ reversed ? 'map-left' : 'map-right' }
						options={ [
							{ label: 'Mapa levo, tekst desno', value: 'map-left' },
							{ label: 'Tekst levo, mapa desno', value: 'map-right' },
						] }
						onChange={ ( v ) => setAttributes( { reversed: v === 'map-left' } ) }
					/>
				</PanelBody>

				<PanelBody title="Google Maps">
					<TextareaControl
						label="iframe kod (zalepi iz Google Maps → Deli → Ugradi kartu)"
						value={ mapEmbed }
						onChange={ ( v ) => setAttributes( { mapEmbed: v } ) }
						placeholder='<iframe src="https://www.google.com/maps/embed?pb=…" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>'
						rows={ 5 }
						help={ mapEmbed && ! mapSrc ? '⚠ Nije pronađen src atribut — zalepi ceo <iframe> tag.' : '' }
					/>
				</PanelBody>

				<PanelBody title="Naslov" initialOpen={ false }>
					<FontSizePicker
						fontSizes={ FONT_SIZES }
						value={ titleFontSize }
						onChange={ ( v ) => setAttributes( { titleFontSize: v ?? '2.5rem' } ) }
					/>
					<SelectControl
						label="Poravnanje"
						value={ titleAlign }
						options={ ALIGN_OPTIONS }
						onChange={ ( v ) => setAttributes( { titleAlign: v } ) }
					/>
				</PanelBody>

				<PanelBody title="Tekst" initialOpen={ false }>
					<FontSizePicker
						fontSizes={ FONT_SIZES }
						value={ contentFontSize }
						onChange={ ( v ) => setAttributes( { contentFontSize: v ?? '1rem' } ) }
					/>
					<SelectControl
						label="Poravnanje"
						value={ contentAlign }
						options={ ALIGN_OPTIONS }
						onChange={ ( v ) => setAttributes( { contentAlign: v } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="contact-map-col">
					{ mapSrc ? (
						<iframe
							src={ mapSrc }
							style={ { width: '100%', height: '100%', border: 'none', display: 'block' } }
							loading="lazy"
							title="Google Maps"
						/>
					) : (
						<div className="contact-map-placeholder">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" width="48" height="48">
								<path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
								<circle cx="12" cy="9" r="2.5"/>
							</svg>
							<p>Dodajte Google Maps iframe<br/>iz bočne trake</p>
						</div>
					) }
				</div>

				<div className="contact-text-col">
					<div className="contact-text">
						<RichText
							tagName="h2"
							className="contact-title"
							value={ title }
							onChange={ ( v ) => setAttributes( { title: v } ) }
							placeholder="Kontaktirajte nas…"
							style={ { fontSize: titleFontSize, textAlign: titleAlign } }
						/>

						<div className="contact-detail-group">
							<span className="contact-group-label">Radno vreme</span>
							<RichText
								tagName="div"
								className="contact-hours"
								value={ workingHours }
								onChange={ ( v ) => setAttributes( { workingHours: v } ) }
								placeholder="Pon–Pet: 09:00–17:00&#xa;Sub: 10:00–14:00&#xa;Ned: Zatvoreno"
								style={ { fontSize: contentFontSize, textAlign: contentAlign } }
							/>
						</div>

						<div className="contact-detail-group">
							<span className="contact-group-label">Kontakt info</span>
							<RichText
								tagName="div"
								className="contact-info"
								value={ contactInfo }
								onChange={ ( v ) => setAttributes( { contactInfo: v } ) }
								placeholder="Ul. primer 12, Beograd&#xa;+381 11 000 0000&#xa;info@primer.rs"
								style={ { fontSize: contentFontSize, textAlign: contentAlign } }
							/>
						</div>
					</div>
				</div>
			</section>
		</>
	);
}

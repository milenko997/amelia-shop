import {
	useBlockProps,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
	MediaPlaceholder,
	RichText,
} from '@wordpress/block-editor';
import {
	PanelBody,
	RadioControl,
	FontSizePicker,
	SelectControl,
	Button,
	ResponsiveWrapper,
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

export default function Edit( { attributes, setAttributes } ) {
	const {
		title, content,
		mediaId, mediaUrl, mediaAlt,
		reversed,
		titleFontSize, contentFontSize,
		titleAlign, contentAlign,
	} = attributes;

	const blockProps = useBlockProps( {
		className: `about-us-section${ reversed ? ' is-reversed' : '' }`,
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title="Raspored">
					<RadioControl
						selected={ reversed ? 'reversed' : 'normal' }
						options={ [
							{ label: 'Tekst levo, slika desno', value: 'normal' },
							{ label: 'Slika levo, tekst desno', value: 'reversed' },
						] }
						onChange={ ( v ) => setAttributes( { reversed: v === 'reversed' } ) }
					/>
				</PanelBody>

				<PanelBody title="Slika">
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) => setAttributes( {
								mediaId:  media.id,
								mediaUrl: media.url,
								mediaAlt: media.alt || media.title,
							} ) }
							allowedTypes={ [ 'image' ] }
							value={ mediaId }
							render={ ( { open } ) => (
								<>
									{ mediaUrl && (
										<ResponsiveWrapper naturalWidth={ 16 } naturalHeight={ 9 }>
											<img src={ mediaUrl } alt={ mediaAlt } style={ { display: 'block', width: '100%' } } />
										</ResponsiveWrapper>
									) }
									<Button
										onClick={ open }
										variant="secondary"
										style={ { marginTop: mediaUrl ? '0.5rem' : 0, width: '100%', justifyContent: 'center' } }
									>
										{ mediaUrl ? 'Zameni sliku' : 'Dodaj sliku' }
									</Button>
								</>
							) }
						/>
					</MediaUploadCheck>
					{ mediaUrl && (
						<Button
							onClick={ () => setAttributes( { mediaId: 0, mediaUrl: '', mediaAlt: '' } ) }
							variant="link"
							isDestructive
							style={ { marginTop: '0.35rem' } }
						>
							Ukloni sliku
						</Button>
					) }
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
				<div className="about-us-text-col">
					<div className="about-us-text">
						<RichText
							tagName="h2"
							className="about-us-title"
							value={ title }
							onChange={ ( v ) => setAttributes( { title: v } ) }
							placeholder="Naslov…"
							style={ { fontSize: titleFontSize, textAlign: titleAlign } }
						/>
						<RichText
							tagName="div"
							className="about-us-content"
							value={ content }
							onChange={ ( v ) => setAttributes( { content: v } ) }
							placeholder="Opišite vašu prodavnicu…"
							style={ { fontSize: contentFontSize, textAlign: contentAlign } }
						/>
					</div>
				</div>

				<div className="about-us-image-col">
					{ mediaUrl ? (
						<MediaUploadCheck>
							<MediaUpload
								onSelect={ ( media ) => setAttributes( {
									mediaId:  media.id,
									mediaUrl: media.url,
									mediaAlt: media.alt || media.title,
								} ) }
								allowedTypes={ [ 'image' ] }
								value={ mediaId }
								render={ ( { open } ) => (
									<div className="about-us-image-wrap">
										<img src={ mediaUrl } alt={ mediaAlt } />
										<div className="about-us-image-replace">
											<Button variant="secondary" onClick={ open }>
												Zameni sliku
											</Button>
										</div>
									</div>
								) }
							/>
						</MediaUploadCheck>
					) : (
						<MediaPlaceholder
							labels={ { title: 'Slika prodavnice' } }
							onSelect={ ( media ) => setAttributes( {
								mediaId:  media.id,
								mediaUrl: media.url,
								mediaAlt: media.alt || media.title,
							} ) }
							accept="image/*"
							allowedTypes={ [ 'image' ] }
						/>
					) }
				</div>
			</section>
		</>
	);
}

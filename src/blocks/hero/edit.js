import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl }                     from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const {
		badge, title, titleItalic, description,
		primaryButtonText, primaryButtonUrl,
		secondaryButtonText, secondaryButtonUrl,
	} = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title="Primarno dugme">
					<TextControl
						label="Tekst"
						value={ primaryButtonText }
						onChange={ ( v ) => setAttributes( { primaryButtonText: v } ) }
					/>
					<TextControl
						label="URL"
						value={ primaryButtonUrl }
						onChange={ ( v ) => setAttributes( { primaryButtonUrl: v } ) }
					/>
				</PanelBody>
				<PanelBody title="Sekundarno dugme">
					<TextControl
						label="Tekst"
						value={ secondaryButtonText }
						onChange={ ( v ) => setAttributes( { secondaryButtonText: v } ) }
					/>
					<TextControl
						label="URL"
						value={ secondaryButtonUrl }
						onChange={ ( v ) => setAttributes( { secondaryButtonUrl: v } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<section { ...useBlockProps( { className: 'hero' } ) }>
				<div className="hero-content">
					<RichText
						tagName="span"
						className="hero-badge"
						value={ badge }
						onChange={ ( v ) => setAttributes( { badge: v } ) }
						placeholder="Bedž tekst…"
					/>
					<h1>
						<RichText
							tagName="span"
							value={ title }
							onChange={ ( v ) => setAttributes( { title: v } ) }
							placeholder="Naslov…"
						/>
						<br />
						<em>
							<RichText
								tagName="span"
								value={ titleItalic }
								onChange={ ( v ) => setAttributes( { titleItalic: v } ) }
								placeholder="Kurziv deo naslova…"
							/>
						</em>
					</h1>
					<RichText
						tagName="p"
						value={ description }
						onChange={ ( v ) => setAttributes( { description: v } ) }
						placeholder="Opis…"
					/>
					<div className="hero-actions">
						<span className="btn">{ primaryButtonText }</span>
						<span className="btn btn-outline">{ secondaryButtonText }</span>
					</div>
				</div>
			</section>
		</>
	);
}

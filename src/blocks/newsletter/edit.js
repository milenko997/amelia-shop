import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl }                     from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { title, description, buttonText, placeholder } = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title="Forma">
					<TextControl
						label="Tekst dugmeta"
						value={ buttonText }
						onChange={ ( v ) => setAttributes( { buttonText: v } ) }
					/>
					<TextControl
						label="Placeholder email polja"
						value={ placeholder }
						onChange={ ( v ) => setAttributes( { placeholder: v } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<section { ...useBlockProps( { className: 'newsletter-section' } ) }>
				<div className="container">
					<RichText
						tagName="h2"
						value={ title }
						onChange={ ( v ) => setAttributes( { title: v } ) }
						placeholder="Naslov newslettera…"
					/>
					<RichText
						tagName="p"
						value={ description }
						onChange={ ( v ) => setAttributes( { description: v } ) }
						placeholder="Opis…"
					/>
					<div className="newsletter-form">
						<input type="email" placeholder={ placeholder } disabled />
						<span className="btn">{ buttonText }</span>
					</div>
				</div>
			</section>
		</>
	);
}

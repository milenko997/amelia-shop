import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, Notice }              from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { title, description, buttonText } = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title="Podešavanja forme">
					<TextControl
						label="Tekst dugmeta"
						value={ buttonText }
						onChange={ ( v ) => setAttributes( { buttonText: v } ) }
					/>
					<Notice status="info" isDismissible={ false }>
						Poruke stižu na email iz Izgled → Prilagodi → Podaci o firmi.
					</Notice>
				</PanelBody>
			</InspectorControls>

			<section { ...useBlockProps( { className: 'contact-form-block' } ) }>
				<div className="contact-form-block__inner">
					<RichText
						tagName="h2"
						className="contact-form-block__title"
						value={ title }
						onChange={ ( v ) => setAttributes( { title: v } ) }
						placeholder="Naslov forme…"
					/>
					<RichText
						tagName="p"
						className="contact-form-block__desc"
						value={ description }
						onChange={ ( v ) => setAttributes( { description: v } ) }
						placeholder="Kratak opis…"
					/>

					{ /* Static preview; the real form is rendered by render.php. */ }
					<div className="contact-form" aria-hidden="true">
						<div className="contact-form__row">
							<div className="contact-form__field"><label>Ime i prezime *</label><input type="text" disabled /></div>
							<div className="contact-form__field"><label>Email *</label><input type="email" disabled /></div>
						</div>
						<div className="contact-form__row">
							<div className="contact-form__field"><label>Telefon</label><input type="tel" disabled /></div>
							<div className="contact-form__field"><label>Broj porudžbine</label><input type="text" disabled /></div>
						</div>
						<div className="contact-form__row contact-form__row--full"><div className="contact-form__field"><label>Poruka *</label><textarea rows="5" disabled /></div></div>
						<span className="btn">{ buttonText || 'Pošalji poruku' }</span>
					</div>
				</div>
			</section>
		</>
	);
}

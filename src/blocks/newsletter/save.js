import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function save( { attributes } ) {
	const { title, description, buttonText, placeholder } = attributes;

	return (
		<section { ...useBlockProps.save( { className: 'newsletter-section' } ) }>
			<div className="container">
				<RichText.Content tagName="h2" value={ title } />
				<RichText.Content tagName="p"  value={ description } />
				<form className="newsletter-form" action="#" method="post">
					<input
						type="email"
						name="newsletter_email"
						placeholder={ placeholder }
						required
					/>
					<button type="submit" className="btn">{ buttonText }</button>
				</form>
			</div>
		</section>
	);
}

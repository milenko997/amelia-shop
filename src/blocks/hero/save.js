import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function save( { attributes } ) {
	const {
		badge, title, titleItalic, description,
		primaryButtonText, primaryButtonUrl,
		secondaryButtonText, secondaryButtonUrl,
	} = attributes;

	return (
		<section { ...useBlockProps.save( { className: 'hero' } ) }>
			<div className="hero-content">
				<span className="hero-badge">
					<RichText.Content value={ badge } />
				</span>
				<h1>
					<RichText.Content tagName="span" value={ title } />
					<br />
					<em>{ titleItalic }</em>
				</h1>
				<p>{ description }</p>
				<div className="hero-actions">
					<a href={ primaryButtonUrl } className="btn">
						{ primaryButtonText }
					</a>
					<a href={ secondaryButtonUrl } className="btn btn-outline">
						{ secondaryButtonText }
					</a>
				</div>
			</div>
		</section>
	);
}

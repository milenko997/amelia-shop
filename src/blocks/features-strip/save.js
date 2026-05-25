import { useBlockProps, RichText } from '@wordpress/block-editor';

const SVG_ICONS = {
	truck:   <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>,
	refresh: <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>,
	lock:    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>,
	phone:   <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.67A2 2 0 012 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>,
};

export default function save( { attributes } ) {
	const { features } = attributes;

	return (
		<section { ...useBlockProps.save( { className: 'features-strip' } ) }>
			<div className="features-grid container">
				{ features.map( ( feature, i ) => (
					<div key={ i } className="feature-item">
						<div className="feature-icon">
							{ SVG_ICONS[ feature.icon ] || SVG_ICONS.truck }
						</div>
						<div className="feature-text">
							<RichText.Content tagName="strong" value={ feature.title } />
							<RichText.Content tagName="span"   value={ feature.subtitle } />
						</div>
					</div>
				) ) }
			</div>
		</section>
	);
}

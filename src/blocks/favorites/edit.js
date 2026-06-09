import { useBlockProps } from '@wordpress/block-editor';

export default function Edit() {
	const blockProps = useBlockProps( { className: 'favorites-block' } );

	return (
		<section { ...blockProps }>
			<div className="container">
				<p style={ { textAlign: 'center', padding: '2rem', color: '#888', fontSize: '0.9rem' } }>
					<strong>Sačuvani proizvodi</strong> — prikazuju se na osnovu localStorage korisnika.
				</p>
			</div>
		</section>
	);
}

import { useBlockProps, RichText } from '@wordpress/block-editor';
import { Button }                  from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { title, items } = attributes;

	const updateItem = ( index, key, value ) => {
		setAttributes( {
			items: items.map( ( item, i ) =>
				i === index ? { ...item, [ key ]: value } : item
			),
		} );
	};

	const addItem = () => {
		setAttributes( { items: [ ...items, { question: '', answer: '' } ] } );
	};

	const removeItem = ( index ) => {
		setAttributes( { items: items.filter( ( _, i ) => i !== index ) } );
	};

	const moveItem = ( index, direction ) => {
		const updated = [ ...items ];
		const target  = index + direction;
		if ( target < 0 || target >= updated.length ) return;
		[ updated[ index ], updated[ target ] ] = [ updated[ target ], updated[ index ] ];
		setAttributes( { items: updated } );
	};

	return (
		<section { ...useBlockProps( { className: 'faq-block' } ) }>
			<div className="container">
				<RichText
					tagName="h2"
					className="faq-block__title"
					value={ title }
					onChange={ ( v ) => setAttributes( { title: v } ) }
					placeholder="Naslov sekcije…"
				/>

				<div className="faq-block__list">
					{ items.map( ( item, i ) => (
						<div key={ i } className="faq-block__item is-open faq-editor-item">
							<div className="faq-editor-item__header">
								<RichText
									tagName="p"
									className="faq-editor-item__question"
									value={ item.question }
									onChange={ ( v ) => updateItem( i, 'question', v ) }
									placeholder="Unesite pitanje…"
								/>
								<div className="faq-editor-item__actions">
									<Button
										icon="arrow-up-alt2"
										isSmall
										disabled={ i === 0 }
										onClick={ () => moveItem( i, -1 ) }
										label="Pomeri gore"
									/>
									<Button
										icon="arrow-down-alt2"
										isSmall
										disabled={ i === items.length - 1 }
										onClick={ () => moveItem( i, 1 ) }
										label="Pomeri dole"
									/>
									<Button
										icon="trash"
										isSmall
										isDestructive
										onClick={ () => removeItem( i ) }
										label="Ukloni"
									/>
								</div>
							</div>
							<RichText
								tagName="p"
								className="faq-editor-item__answer"
								value={ item.answer }
								onChange={ ( v ) => updateItem( i, 'answer', v ) }
								placeholder="Unesite odgovor…"
							/>
						</div>
					) ) }
				</div>

				<button
					className="faq-editor-add"
					onClick={ ( e ) => { e.preventDefault(); addItem(); } }
				>
					<span className="faq-editor-add__icon">+</span>
					Dodaj pitanje
				</button>
			</div>
		</section>
	);
}

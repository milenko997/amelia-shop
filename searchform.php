<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>"
	style="display:flex;gap:.75rem;padding:1rem 0;">
	<input type="search"
		class="search-field"
		placeholder="Pretraži proizvode…"
		value="<?php echo get_search_query(); ?>"
		name="s"
		style="flex:1;padding:.75rem 1.25rem;border:2px solid var(--color-border);border-radius:var(--radius-full);font-family:var(--font-body);font-size:.9rem;"
	>
	<button type="submit" class="btn" style="border-radius:var(--radius-full);">
		Pretraži
	</button>
</form>

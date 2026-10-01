document.addEventListener('DOMContentLoaded', function () {
	const searchInput = document.getElementById('SearchBar');
	const table = document.querySelector('.table-responsive table');

	if (!searchInput || !table) {
		return;
	}

	const tableBody = table.querySelector('tbody');
	const clientRows = Array.from(tableBody.querySelectorAll('tr'))
		.filter(row => row.querySelector('.check-cliente'));

	const normalizeText = text => text
		.normalize('NFD')
		.replace(/[\u0300-\u036f]/g, '')
		.toLowerCase();

	const noResultsRow = document.createElement('tr');
	const noResultsCell = document.createElement('td');
	noResultsCell.colSpan = table.querySelectorAll('thead th').length;
	noResultsCell.className = 'text-center text-muted';
	noResultsCell.textContent = 'No se encontraron resultados que coincidan.';
	noResultsRow.appendChild(noResultsCell);
	noResultsRow.hidden = true;
	tableBody.appendChild(noResultsRow);

	searchInput.addEventListener('input', function () {
		const query = normalizeText(searchInput.value.trim());
		let visibleRows = 0;

		clientRows.forEach(row => {
			const matches = normalizeText(row.textContent).includes(query);
			row.hidden = !matches;
			visibleRows += matches ? 1 : 0;
		});

		noResultsRow.hidden = query.length === 0 || visibleRows > 0 || clientRows.length === 0;
	});
});

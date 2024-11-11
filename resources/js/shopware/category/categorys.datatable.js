import CustomeDatatables from '/resources/js/partials/datatables';

if(document.getElementById('categorysDatatable')) {
    const columns = [
        { label: 'Nummer', field: 'id', width: 35},
        { label: 'Titel', field: 'title' },
        { label: 'Sync', field: 'status', width: 50 },
        { label: 'Aktiviert', field: 'active', width: 50 },
        { label: 'Datum', field: 'created_at', width: 300 },
        { label: 'Aktion', field: 'action', sort: false, width: 150 }
    ];

    const CategoryTable = new CustomeDatatables(
        columns,
        'datatable-search-input',
        'categorysDatatable',
        (category) => ({
            ...category,
            id: parseInt(category.id),
            title: category.title,
            status: category.status,
            active: category.active,
            created_date: Date.parse(category.created_at),
            action: `<a href="/shopware/kategorie/editieren/${category.id}" class="btn btn-primary btn-sm">Details</a>`
        }),
        '/shopware/kategorie/json/getCategory/'
    );
}

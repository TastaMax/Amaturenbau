import CustomeDatatables from '/resources/js/partials/datatables';

if(document.getElementById('productclassDatatable')) {
    const columns = [
        { label: 'Nummer', field: 'id', width: 35},
        { label: 'Titel', field: 'title' },
        { label: 'Sync', field: 'status', width: 50 },
        { label: 'Datum', field: 'created_at', width: 300 },
        { label: 'Aktion', field: 'action', sort: false, width: 150 }
    ];

    const ProductclassTable = new CustomeDatatables(
        columns,
        'datatable-search-input',
        'productclassDatatable',
        (productclass) => ({
            ...productclass,
            id: parseInt(productclass.id),
            title: productclass.title,
            status: productclass.status,
            created_date: Date.parse(productclass.created_at),
            action: `<a href="/shopware/produktklasse/editieren/${productclass.id}" class="btn btn-primary btn-sm">Details</a>`
        }),
        '/shopware/produktklasse/json/getProductclass/'
    );
}

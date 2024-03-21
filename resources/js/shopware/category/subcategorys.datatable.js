import CustomeDatatables from '/resources/js/partials/datatables';
import {getIdUrl} from '/resources/js/partials/getIdUrl';

if(document.getElementById('subCategorysDatatable')) {
    let idCategory = getIdUrl();

    const subColumns = [
        { label: 'Nummer', field: 'id', width: 35},
        { label: 'Titel', field: 'title' },
        { label: 'Sync', field: 'status', width: 50 },
        { label: 'Datum', field: 'created_at', width: 300 },
        { label: 'Aktion', field: 'action', sort: false, width: 150 }
    ];

    const SubCategoryTable = new CustomeDatatables(
        subColumns,
        'datatable-search-input-subcategory',
        'subCategorysDatatable',
        (subcategory) => ({
            ...subcategory,
            id: parseInt(subcategory.id),
            title: subcategory.title,
            status: subcategory.status,
            created_date: Date.parse(subcategory.created_at),
            action: `<a href="/shopware/kategorie/editieren/${subcategory.id}" class="btn btn-primary btn-sm">Details</a>`
        }),
        '/shopware/kategorie/json/getSubCategory/'+idCategory
    );
}

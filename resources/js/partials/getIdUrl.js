export function getIdUrl() {
    const url = window.location.href;
    const path = new URL(url).pathname;
    const segments = path.split('/');
    const lastSegment = segments[segments.length - 1];
    const lastNumber = parseInt(lastSegment);

    if (!isNaN(lastNumber)) {
        console.log("Nummer: " + lastNumber);
    } else {
        console.log("Es konnte keine Nummer gefunden werden.", url, path, segments, lastSegment, lastNumber);
    }

    return lastNumber;
}

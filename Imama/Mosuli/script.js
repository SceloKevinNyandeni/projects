function handleButtonClick(selector) {
    const section = document.querySelector(selector);
    if (section) {
        section.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });
    }
}

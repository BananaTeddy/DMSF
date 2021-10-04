;dmsf.modal = function modal(modalWindow, onClose = null) {

    let modalBackground = document.createElement('div')
    
    modalBackground.classList.add('modal--background')
    
    modalBackground.addEventListener('click', _event => {
    
        if (_event.target == modalBackground) {
    
            if  (onClose != null) {
                onClose()
            }
            modalBackground.remove()
    
        }
    })
    
    modalWindow.classList.add('modal--window')
    modalBackground.appendChild(modalWindow)

    document.body.appendChild(modalBackground)

    return modalBackground
}
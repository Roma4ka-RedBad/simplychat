function showhide() {
    let input_field = document.getElementById('password')
    if (input_field.getAttribute('type') === 'password') {
        input_field.setAttribute('type', 'text')
    } else {
        input_field.setAttribute('type', 'password')
    }
}

function toast(headerText, bodyText, circleColor = '#fb5151') {
    document.getElementById('toast-header-text').textContent = headerText
    document.getElementById('toast-body-text').textContent = bodyText
    document.querySelector('.toast-circle').style.backgroundColor = circleColor
    let toast = bootstrap.Toast.getOrCreateInstance(
        document.getElementById('toast')
    )
    toast.show()
}

document.addEventListener('DOMContentLoaded', () => {
    let users = document.querySelectorAll('#user');

    users.forEach((user) => {

        user.addEventListener('click', (event) => {
            // cancel the default behavior
            event.preventDefault();
            event.stopImmediatePropagation();
            // redirect to the user page
            window.location.href = '/profile/' + user.getAttribute('user-id');
        });
    });
});

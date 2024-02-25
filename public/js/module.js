const module = {
    init() {
        this.api = "http://localhost"
        this.selectedId = 0
        this.cacheDom()
        this.render()
        this.methods()
    },
    container: $('body'),
    cacheDom() {
        this.btnAdd = this.container.find('#btn-add')
        this.btnSubmit = this.container.find('#submit')
        this.btnUpdate = this.container.find('#update')
        // this.btnDelete = this.container.find('#btn-delete')
        this.thumbView = this.container.find('#thumb-view')
        this.tbody = this.container.find('#t-body')

        // Form
        this.form = this.container.find('#form')
        this.title = this.container.find('[name=title]')
        this.thumbnail = this.container.find('[name=thumbnail]')
        this.filename = this.container.find('[name=filename]')

        // Modal
        this.modal = this.container.find('#modal')
        this.close = this.container.find('#close')
    },
    http(option) {
        return $.ajax(option)
            .fail((err) => {
                switch (err.status) {
                    case 422:
                        return alert(err.responseJSON.data.join(','))
                    default:
                        return alert(err.responseJSON.message)
                }
            })
    },
    render() {
        this.tbody.empty()

        const options = {
            url: `${this.api}/api/routes/file/list.php`,
            type: "GET",
            dataType: "json",
            headers: {
                "Accept": "application/json",
            }
        };

        this.http(options)
            .done((res) => {

                res.data.forEach((item) => {
                    this.tbody.append(`
                                <tr>
                                    <td>${item.title}</td>
                                    <td class="thumbnail">
                                        <img src="${item.thumb}?${new Date().getTime()}" alt="${item.filename}" width="30" height="30">
                                    </td>
                                    <td>${item.filename}</td>
                                    <td>${item.created_at}</td>
                                    <td class="action-buttons">
                                        <button id="btn-view" data-id="${item.id}">View</button>
                                        <button id="btn-edit" data-id="${item.id}">Edit</button>
                                        <button id="btn-delete" data-id="${item.id}">Delete</button>
                                    </td>
                                </tr>
                            `)
                })

            })
    },
    methods() {
        this.btnAdd.on('click', (e) => {
            e.preventDefault()
            this.modal.css('display', 'block')
            this.thumbnail.css('display', 'block')
            this.btnSubmit.css('display', 'block')
            this.title.val('')
            this.filename.val('')
        })

        this.close.on('click', (e) => {
            e.preventDefault()
            this.modal.css('display', 'none')
            this.thumbnail.css('display', 'none')
            this.thumbView.css('display', 'none')
            this.btnSubmit.css('display', 'none')
            this.btnUpdate.css('display', 'none')

            this.title.attr('readonly', false)
            this.filename.attr('readonly', false)
        })

        this.btnSubmit.on('click', (e) => {
            e.preventDefault()
            const formData = new FormData()
            formData.append('title', this.title.val())
            formData.append('thumb', this.thumbnail[0].files[0])
            formData.append('filename', this.filename.val())
            const options = {
                url: `${this.api}/api/routes/file/create.php`,
                type: "POST",
                dataType: "json",
                headers: {
                    "Accept": "application/json",
                },
                data: formData,
                contentType: false,
                processData: false,
            };

            this.http(options).done(() => {
                this.modal.css('display', 'none')
                this.btnSubmit.css('display', 'none')
                this.btnUpdate.css('display', 'none')
                this.render()
            })
        })

        this.btnUpdate.on('click', (e) => {
            e.preventDefault()
            const formData = new FormData()
            formData.append('title', this.title.val())
            formData.append('thumb', this.thumbnail[0].files[0])
            formData.append('filename', this.filename.val())
            const options = {
                url: `${this.api}/api/routes/file/update.php?id=${this.selectedId}`,
                type: "POST",
                dataType: "json",
                headers: {
                    "Accept": "application/json",
                },
                data: formData,
                contentType: false,
                processData: false,
            };

            this.http(options).done(() => {
                this.modal.css('display', 'none')
                this.btnUpdate.css('display', 'none')
                this.selectedId = 0;
                this.render()
            })
        })

        $(document).on('click', '#btn-view', (e) => {
            e.preventDefault()
            const options = {
                url: `${this.api}/api/routes/file/show.php?id=${e.target.dataset.id}`,
                type: "GET",
                dataType: "json",
                headers: {
                    "Accept": "application/json",
                },
            };

            this.http(options).then((res) => {
                this.modal.css('display', 'block')
                this.thumbView.css('display', 'block')

                this.thumbView.attr('src', res.data.thumb)
                this.title.attr('readonly', true)
                this.filename.attr('readonly', true)

                this.title.val(res.data.title)
                this.filename.val(res.data.filename)
            })
        })

        $(document).on('click', '#btn-edit', (e) => {
            e.preventDefault()
            this.selectedId = e.target.dataset.id
            this.editing = true
            const options = {
                url: `${this.api}/api/routes/file/show.php?id=${e.target.dataset.id}`,
                type: "GET",
                dataType: "json",
                headers: {
                    "Accept": "application/json",
                },
            };

            this.http(options).then((res) => {
                this.modal.css('display', 'block')
                this.thumbnail.css('display', 'block')
                this.btnUpdate.css('display', 'block')
                this.title.val(res.data.title)
                this.filename.val(res.data.filename)
            })
        })

        $(document).on('click', '#btn-delete', (e) => {
            e.preventDefault()
            const options = {
                url: `${this.api}/api/routes/file/delete.php?id=${e.target.dataset.id}`,
                type: "DELETE",
                dataType: "json",
                headers: {
                    "Accept": "application/json",
                },
            };

            this.http(options).then((res) => this.render())
        })
    },
}

$(document).ready(() => module.init());
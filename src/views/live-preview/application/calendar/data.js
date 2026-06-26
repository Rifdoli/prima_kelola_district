const categories = [
    {
        name: 'Danger',
        value: 'bg-danger'
    },
    {
        name: 'Success',
        value: 'bg-success'
    },
    {
        name: 'Primary',
        value: 'bg-primary'
    },
    {
        name: 'Info',
        value: 'bg-info'
    },
    {
        name: 'Dark',
        value: 'bg-dark'
    },
    {
        name: 'Warning',
        value: 'bg-warning'
    },
];

const calendarEvents = [
    {
        id: 1,
        title: 'Hey!',
        start: new Date().setDate(new Date().getDate() + 2),
        className: 'fc-event fc-event-draggable fc-event-resizable fc-event-start fc-event-end fc-event-past event-primary fc-daygrid-event fc-daygrid-block-event fc-h-event',
    },
    {
        id: 2,
        title: 'See John Deo',
        start: new Date(),
        end: new Date(),
        className: 'fc-event fc-event-draggable fc-event-resizable fc-event-start fc-event-end fc-event-past event-warning fc-daygrid-event fc-daygrid-block-event fc-h-event',
    },
    {
        id: 3,
        title: 'Meet John Deo',
        start: new Date().setDate(new Date().getDate() + 2),
        className: 'fc-event fc-event-draggable fc-event-resizable fc-event-start fc-event-end fc-event-past event-danger fc-daygrid-event fc-daygrid-dot-event',
    },
    {
        id: 4,
        title: 'Buy a Theme',
        start: new Date().setDate(new Date().getDate() + 4),
        className: 'fc-event fc-event-draggable fc-event-resizable fc-event-start fc-event-end fc-event-future fc-daygrid-event fc-daygrid-block-event fc-h-event'
    }
];

export { categories, calendarEvents };
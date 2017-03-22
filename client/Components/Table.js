

import React from 'react'
import xhr from 'xhr'

const apiEndpoint = './api';
const filters = {
    'equal': '=',
    'bigger': '>',
    'less': '<',
    'contains': 'contains'
};


class Table extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            items: [],
            filters: {},
        }
    }

    componentDidMount () {
        this.handleReload();
    }

    handleReload() {
        xhr.get(apiEndpoint+'/items'+(typeof this.state.filters.parameter !== 'undefined'
                ? '/'+(`${this.state.filters.parameter}-${this.state.filters.type}-${this.state.filters.value}`)
                : ''), (err, res) => this.setState({
            items: this.parseItems(res.body),
            filters: this.state.filters
        }));
    }

    handleFilterItems(e) {
        e.preventDefault();
        this.state.filters = {
            parameter: e.target.parameter.value,
            type: e.target.type.value,
            value: e.target.value.value,
        };
        this.handleReload();
    }

    handleRemoveFilter(e) {
        e.preventDefault();
        this.state.filters = {};
        this.handleReload();
    }

    parseItems(response) {
        return JSON.parse(response).map(item => ({
            id: item.id,
            props: item.props ? JSON.parse(item.props) : null
        }));
    }

    handleAddItem(e) {
        e.preventDefault();
        xhr.post(apiEndpoint+'/items', (err, res) => this.handleReload());
    }

    handleAddParameter(id, e) {
        e.preventDefault();
        xhr.patch(apiEndpoint+'/items/'+id, {
            body: JSON.stringify({name: e.target.name.value, value: e.target.value.value})
        }, (err, res) => this.handleReload());
        e.target.name.value = '';
        e.target.value.value = '';
    }

    handleRemoveItem(id, p, e) {
        e.preventDefault();
        xhr.del(apiEndpoint+'/items/'+id, (err, res) => this.handleReload());
    }

    handleRemoveParameter(id, param, p, e) {
        e.preventDefault();
        xhr.del(apiEndpoint+'/items/'+id+'/'+param, (err, res) => this.handleReload());
    }

    render() {
        return (
            <div>
                <button onClick={this.handleAddItem.bind(this)} className="btn btn-success">
                    <i className="fa fa-plus"></i> add item
                </button>
                <form onSubmit={this.handleFilterItems.bind(this)} className="form form-inline">
                    <div className="form-group">
                        <input type="text" name="parameter"  placeholder="parameter" className="form-control" />
                    </div>
                    <div className="form-group">
                        <select className="form-control" name="type"  placeholder="- select rule -" required>
                            { Object.keys(filters).map((key) => (
                                <option value={key} key={key}>{filters[key]}</option>
                            )) }
                        </select>
                    </div>
                    <div className="form-group">
                        <input type="text"  name="value" className="form-control" placeholder="value" required />
                    </div>
                    <div className="form-group">
                        <input type="submit" className="btn btn-success" name="submit" />
                    </div>
                    <div className="form-group">
                        <button className="btn btn-danger" onClick={this.handleRemoveFilter.bind(this)}>
                            <i className="fa fa-remove"></i> clear filter
                        </button>
                    </div>
                </form>
                <table className="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>id</th>
                            <th colSpan="2">properties</th>
                            <th>actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        { this.state.items.map(item =>
                            <tr key={item.id}>
                                <td>{item.id}</td>
                                <td>
                                    { item.props ? (
                                            <ul>
                                                { Object.keys(item.props).map((key, index) => (
                                                    <li key={key}>
                                                        {key}: {item.props[key]}&nbsp;
                                                        <button onClick={this.handleRemoveParameter.bind(this, item.id, key)} className="btn btn-xs btn-danger">
                                                            <i className="fa fa-remove"></i>
                                                        </button>
                                                    </li>
                                                )) }
                                            </ul>
                                    ) : null }
                                </td>
                                <td>
                                    <form className="form-inline" onSubmit={this.handleAddParameter.bind(this, item.id)}>
                                        <div className="form-group">
                                            <input name="name" pattern="\w+" type="text" className="form-control" placeholder="key" required="required" />
                                        </div>
                                        <div className="form-group">
                                            <input name="value" type="text" className="form-control" placeholder="value" required="required" />
                                        </div>
                                        <div className="form-group">
                                            <input type="submit" className="btn btn-success" value="add" />
                                        </div>
                                    </form>
                                </td>
                                <td>
                                    <button onClick={this.handleRemoveItem.bind(this, item.id)} className="btn btn-danger">
                                        <i className="fa fa-remove"></i> remove
                                    </button>
                                </td>
                            </tr>
                        ) }
                    </tbody>
                </table>
            </div>
        )
    }
}

export default Table;

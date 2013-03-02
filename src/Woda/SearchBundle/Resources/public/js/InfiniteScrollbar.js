function InfiniteScrollbar(content, opts) {
    this.content = content;
    this.options = opts;
    this.pos = 0;
    this.url = '';

    this.__urlize();
    this.content.bind('afterscroll', { me: this }, this.afterscrollHander);
}

InfiniteScrollbar.prototype.scroll = function(x, y) {
    this.content.scrollTop(this.content.scrollTop() - y);
    this.pos = this.content.height() + this.content.scrollTop();
    this.content.trigger('afterscroll');
}

InfiniteScrollbar.prototype.__urlize = function() {
    var attributes = this.options.url.match(/\{\w+}/g),
        attribute;

    this.url = this.options.url;
    for (var idx = 0, length = attributes.length ; idx < length ; ++idx) {
        attribute = attributes[idx].replace('{', '').replace('}', '');
        this.url = this.url.replace('{' + attribute + '}', this.options[attribute]);
    }

    return (this.url);
}

InfiniteScrollbar.prototype.__interpretTemplate = function(data) {
    if (!this.options.template) {
        return ('<p>' + data + '</p>');
    }

    var attributes = this.options.template.match(/\{\w+}/g),
        attribute,
        str;

        str = this.options.template;
    for (var idx = 0, length = attributes.length ; idx < length ; ++idx) {
        attribute = attributes[idx].replace('{', '').replace('}', '');
        str = str.replace('{' + attribute + '}', data[attribute]);
    }

    return (str);
}

InfiniteScrollbar.prototype.begin = function() {
    this.scroll(0, 0);
}

InfiniteScrollbar.prototype.end = function() {
    this.scroll(0, - this.content[0].scrollHeight);
}

InfiniteScrollbar.prototype.afterscrollHander = function(e) {
    var me = e.data.me;

    if (me.options.autoload == true) {
        if (((me.pos * 100) / me.content[0].scrollHeight) >= me.options.progress) {
            $.ajax({
                url: me.__urlize(),
                data: me.options.extraParams,
                dataType: 'json',
                success: function(data) {
                    console.debug('data: ', data);

                    if (data.error) {
                        this.scope.content.append(data.error);
                    } else {
                        for (var i = 0 ; i < data.data.length ; ++i) {
                            this.scope.content.append(this.scope.__interpretTemplate(data.data[i]));
                        }

                        if (this.scope.options.counter) {
                            this.scope.options.counter.html(data.count);
                        }

                        this.scope.options.offset = data.offset;
                        this.scope.options.length = data.length;
                    }
                },
                failure: function() {
                    alert('ERREUR');
                    // TODO
                },
                scope: me
            });
        }
    }
}
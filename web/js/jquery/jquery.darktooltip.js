/*
 * DarkTooltip v0.1.8
 * Simple customizable tooltip with confirm option and 3d effects
 * (c)2014 Ruben Torres - rubentdlh@gmail.com
 * Released under the MIT license
 */
(function($)
{
  function DarkTooltip(element, options)
  {
    this.bearer  = element;
    this.options = options;
    this.hideEvent;
    this.mouseOverMode = (this.options.trigger == 'hover' || this.options.trigger == 'mouseover' || this.options.trigger == 'onmouseover');
  }

  DarkTooltip.prototype =
  {
    show : function()
    {
      var dt = this;

      // Close all other tooltips
      this.tooltip.css('display', 'block');

      // Set event to prevent tooltip from closig when mouse is over the tooltip
      if (dt.mouseOverMode)
      {
        this.tooltip.mouseover(function()
        {
          clearTimeout(dt.hideEvent);
        });

        this.tooltip.mouseout(function()
        {
          clearTimeout(dt.hideEvent);
          dt.hide();
        });
      }
    },

    hide : function()
    {
      var dt = this;

      this.hideEvent = setTimeout(function()
      {
        dt.tooltip.hide();
      }, 100);
    },

    toggle : function()
    {
      if (this.tooltip.is(':visible'))
      {
        this.hide();
      }
      else
      {
        this.show();
      }
    },

    addAnimation : function()
    {
      switch(this.options.animation)
      {
        case 'none':
          break;

        case 'fadeIn':
          this.tooltip.addClass('animated');
          this.tooltip.addClass('fadeIn');
          break;

        case 'flipIn':
          this.tooltip.addClass('animated');
          this.tooltip.addClass('flipIn');
          break;
      }
    },

    setContent : function()
    {
      this.prev_cursor = $(this.bearer).css('cursor');

      $(this.bearer).css('cursor', 'pointer');

      // Get tooltip content
      if (this.options.content)
      {
        this.content = this.options.content;
      }
      else if (this.bearer.attr('data-tooltip'))
      {
        this.content = this.bearer.attr('data-tooltip');
      }
      else
      {
        return;
      }

      if (this.content.charAt(0) == '#')
      {
        $(this.content).hide();
        this.content     = $(this.content).html();
        this.contentType = 'html';
      }
      else
      {
        this.contentType = 'text';
      }

      // Create tooltip container
      this.tooltip = $('<ins class="dark-tooltip ' + this.options.theme + ' ' + this.options.size + ' ' + this.options.gravity + '"><div>' + this.content + '</div><div class="tip"></div></ins>');
      this.tip = this.tooltip.find('.tip');

      $('body').append(this.tooltip);

      // Adjust size for html tooltip
      if (this.contentType == 'html')
      {
        this.tooltip.css('max-width', 'none');
      }

      this.tooltip.css('opacity', this.options.opacity);
      this.addAnimation();

      if (this.options.confirm)
      {
        this.addConfirm();
      }
    },

    setPositions : function()
    {
      var leftPos = this.bearer.offset().left;
      var topPos  = this.bearer.offset().top;

      switch (this.options.gravity)
      {
        case 'south':
          leftPos += this.bearer.outerWidth() / 2 - this.tooltip.outerWidth() / 2;
          topPos  += -this.tooltip.outerHeight() - this.tip.outerHeight() / 2;
          break;

        case 'west':
          leftPos += this.bearer.outerWidth() + this.tip.outerWidth() / 2;
          topPos  += this.bearer.outerHeight() / 2 - (this.tooltip.outerHeight() / 2);
          break;

        case 'north':
          leftPos += this.bearer.outerWidth() / 2 - (this.tooltip.outerWidth() / 2);
          topPos  += this.bearer.outerHeight() + this.tip.outerHeight() / 2;
          break;

        case 'east':
          leftPos += -this.tooltip.outerWidth() - this.tip.outerWidth() / 2;
          topPos  += this.bearer.outerHeight() / 2 - this.tooltip.outerHeight() / 2;
          break;
      }

      this.tooltip.css('left', leftPos);
      this.tooltip.css('top', topPos);
    },

    setEvents : function()
    {
      var dt = this;

      if (dt.mouseOverMode)
      {
        this.bearer
        .mouseover(function()
        {
          dt.setPositions();
          dt.show();
        })
        .mouseout(function()
        {
          dt.hide();
        });
      }
      else if (this.options.trigger == 'click' || this.options.trigger == 'onclik')
      {
        this.tooltip.click(function(e)
        {
          e.stopPropagation();
        });

        this.bearer.click(function(e)
        {
          e.preventDefault();
          dt.setPositions();
          dt.toggle();
          e.stopPropagation();
        });

        $('html').click(function()
        {
          dt.hide();
        })
      }
    },

    activate : function()
    {
      this.setContent();

      if (this.content)
      {
        this.setEvents();
      }
    },

    destroy: function(element)
    {
      $(this.bearer).css('cursor', this.prev_cursor);

      this.tooltip.remove();

      if (this.mouseOverMode)
      {
         this.bearer.unbind('mouseover mouseout');
      }

      $(element).removeData('darktooltip');
    },

    addConfirm : function()
    {
      this.tooltip.append('<ul class="confirm"><li class="darktooltip-yes">' + this.options.yes + '</li><li class="darktooltip-no">' + this.options.no + '</li></ul>');
      this.setConfirmEvents();
    },

    setConfirmEvents : function()
    {
      var dt = this;

      this.tooltip
      .find('li.darktooltip-yes')
      .click(function(e)
      {
        dt.onYes();
        e.stopPropagation();
      });

      this.tooltip
      .find('li.darktooltip-no')
      .click(function(e)
      {
        dt.onNo();
        e.stopPropagation();
      });
    },

    finalMessage : function()
    {
      if (this.options.finalMessage)
      {
        var dt = this;

        dt.tooltip.find('div:first').html(this.options.finalMessage);
        dt.tooltip.find('ul').remove();
        dt.setPositions();

        setTimeout(function()
        {
          dt.hide();
          dt.setContent();
        }, dt.options.finalMessageDuration);
      }
      else
      {
        this.hide();
      }
    },

    onYes : function()
    {
      this.options.onYes(this.bearer);
      this.finalMessage();
    },

    onNo : function()
    {
      this.options.onNo(this.bearer);
      this.hide();
    }
  }

  $.fn.darkTooltip = function(options)
  {
    this.each(function()
    {
      if ('string' == typeof options)
      {
        if ('destroy' == options)
        {
          var instance = $(this).data('darktooltip');

          if ('undefined' != typeof instance)
          {
            instance.destroy(this);
          }
        }
      }
      else
      {
        options = $.extend({}, $.fn.darkTooltip.defaults, options);
        var tooltip = new DarkTooltip($(this), options);
        tooltip.activate();

        $(this).data('darktooltip', tooltip);
      }
    });
  }

  $.fn.darkTooltip.defaults =
  {
    opacity              : 0.9,
    content              : '',
    size                 : 'medium',
    gravity              : 'south',
    theme                : 'dark',
    trigger              : 'hover',
    animation            : 'none',
    confirm              : false,
    yes                  : 'Yes',
    no                   : 'No',
    finalMessage         : '',
    finalMessageDuration : 1000,
    onYes                : function(){},
    onNo                 : function(){}
  };

})(jQuery);
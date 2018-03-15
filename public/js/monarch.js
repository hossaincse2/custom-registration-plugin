

(function ($) {
    jQuery(document).on('click', '.rejected, .accepted', function(e) {
        e.preventDefault();

        var $this = $(this);


        swal({
            title: "Are you sure?",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
            .then((willDelete) => {
            if (willDelete) {
                window.location.href = $this.attr('data-url');
                // window.location($this.attr('data-url'),'','scrollbars=1,height=500,width=500,left=500,top=100');
            }
    })

    }).on('click', ".view_social a[href='#']", function (e) {
        e.preventDefault();
    });


    function magnific_popup_init() {
        $('.zoom-gallery').magnificPopup({
            delegate: 'a',
            type: 'image',
            closeOnContentClick: false,
            closeBtnInside: true,
            mainClass: 'mfp-with-zoom mfp-img-mobile mfp-title-show',
            image: {
                verticalFit: true,
                titleSrc: function(item) {
                    return item.el.attr('title') + ' &middot; <a class="image-source-link" href="'+item.el.attr('data-source')+'" target="_blank"></a>';
                }
            },
            gallery: {
                enabled: true
            },
            zoom: {
                enabled: true,
                duration: 300, // don't foget to change the duration also in CSS
                opener: function(element) {
                    return element.find('img');
                }
            }

        });
    }

    function get_user_count_val(user_data, role) {
        if(typeof user_data != 'undefined') {
            if(user_data.hasOwnProperty(role)){
                return user_data[role];
            } else {
                return 0;
            }
        }
        return 0;
    }


    $(document).ready(function() {
        magnific_popup_init();
    });


    function user_datatable(tab_id,role,metaValue) {
        $(tab_id + ' table').DataTable({
            "processing": true,
            "serverSide": true,
            "paging": true,
            "Filter": true,
            "bDestroy": true,
            responsive: true,
            "ajax": {
                "url": ajax_object.ajax_url + '?action=get_user_data&page=1&&role='+ role + '&&meta_value=' + metaValue,
                "type": "POST",
            },
            "columns": [
                {"class" : "w150","data": "profile_img"},
                {"data": "full_name_age"},
                {"data": "button"},
                //{ data: "dob[0]", render: $.fn.dataTable.render.number( ',', '.', 0, '$' ) }
            ],
            "columnDefs": [
                {
                    "orderable": false,

                                        // "data": "thumb_nail",
                                        // "render": function ( data, type, row ) {
                                        //     return '<img src="'+data+'" style="width=220px;height=200px;" />';
                                        // },
                    "targets": 0,

                },
                /*{
                    "orderable": false,

                    "data": "thumb_nail",
                    "render": function ( data, type, row ) {
                        return '<a class="accepted  action-accept" data-url="?user_id=<?php echo $user_id->ID. "&_nw_nonce={$nonce}"; ?>&status=subscriber" href="#"><i class="fa fa-check fa-2x custom-like"></i></a>';
                    },
                    "targets": 2,

                },*/
            ],
            "language": {
                "search": "Search By Name:"
            },
            "initComplete": function (settings, data) {
                magnific_popup_init();
                // var api = this.api();
                // api.$('td').click( function () {
                //
                //     api.search( this.innerHTML ).draw();
                // } );
               // var jsonData = jQuery.parseJSON(JSON.stringify(data));
               // var user_data = data.data[0];
                doAjaxRequest();
                // $('a[href="#tab-1519033845342-3-10"]').text('New ('+ new_user +')');
                // $('a[href="#tab-85ef5402-e0c2-7"]').text('Pending ('+ pending +')');
                // $('a[href="#tab-911b11e9-fa1e-3"]').text('Approved ('+ subscriber +')');
                // $('a[href="#tab-1518756218497-2-5"]').text('Reject ('+ reject_user +')');
               // alert('new' + jsonData);
               //  $('a[href="#tab-1519033845342-3-10"]').text('New ('+ get_user_count_val(user_data, 'new_user') +')');
               //  $('a[href="#tab-85ef5402-e0c2-7"]').text('Pending ('+ get_user_count_val(user_data, 'pending') +')');
               //  $('a[href="#tab-911b11e9-fa1e-3"]').text('Approved ('+ get_user_count_val(user_data, 'subscriber') +')');
               //  $('a[href="#tab-1518756218497-2-5"]').text('Reject ('+ get_user_count_val(user_data, 'reject') +')');
            }
        });
    }

    $(document).ready(function () {

        // $('.all_users').DataTable({
        var tab_id = '#tab-1519033845342-3-10';
        var role = 'pending';
        var metaValue = 0;
        user_datatable(tab_id,role,metaValue);
        $(".tabs-nav li a").click(function() {
               tab_id = $(this).attr("href");
            //var id = $("li.tab:not(.ui-tabs-selected)").first().attr("id"); //if you want to fetch an unselected tab
            if(tab_id == '#tab-1519033845342-3-10'){
                 role = 'pending';
                 metaValue = 0;
            }else if(tab_id == '#tab-85ef5402-e0c2-7'){
                 role = 'pending';
                 metaValue = 1;
            }else if(tab_id == '#tab-911b11e9-fa1e-3'){
                 role = 'subscriber';
                 metaValue = 1;
            }else if(tab_id == '#tab-1518756218497-2-5'){
                 role = 'reject';
                 metaValue = 1;
            }
            user_datatable(tab_id,role,metaValue);
            if(metaValue != 0){
                window.location.hash = role;
            } else {
                window.location.hash = 'new';
            }
        });


        $(window).load(function () {
            var $hash = window.location.hash;
            if($hash == '#subscriber'){
                $('a[href="#tab-911b11e9-fa1e-3"]').trigger('click');
            } else if($hash == '#pending') {
                $('a[href="#tab-85ef5402-e0c2-7"]').trigger('click');
            } else if($hash == '#reject') {
                $('a[href="#tab-1518756218497-2-5"]').trigger('click');
            } else {
                $('a[href="#tab-1519033845342-3-10"]').trigger('click');
            }
            $(".ginput_container_date").find('select').each(function(){
                var $this = $(this),
                    $option = $this.find('option[selected="selected"]');
                if($option.length){
                    $this.val($option.text());
                }
            });
         });


    });
    function doAjaxRequest() {
      var data = {
          'action': 'get_user_status',
          'role': 'pending',
          'meta_value': 1,
      };

      // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
      $.post(ajax_object.ajax_url, data, function (response) {
          $('a[href="#tab-1519033845342-3-10"]').text('New ('+ response.new_user +')');
          $('a[href="#tab-85ef5402-e0c2-7"]').text('Pending ('+ response.pending +')');
          $('a[href="#tab-911b11e9-fa1e-3"]').text('Approved ('+ response.subscriber +')');
          $('a[href="#tab-1518756218497-2-5"]').text('Reject ('+ response.reject +')');
          $(".gender-count").remove();
          $( ".q_tabs li:eq( 2 ) a" ).append(function() {
              return "<span class='gender-count'> M"+ response.approved_male +" - F"+ response.approved_female +" </span>";
          });

      });
    }


    $( "#field_16_8 .gfield_description" ).after(function() {
        return profile_image;
     });
    $( "#gform_body" ).after(function() {
        return user_id_input;
     });
    $( ".gform_title" ).append(function() {
        return profile_view;
     });
    $( ".title_subtitle_holder h1" ).after(function() {
        return gender_count;
    });

    // $(document).ready(function () {
    //     var divs = $('.mydivs>div');
    //     var now = 0; // currently shown div
    //     divs.hide().first().show();
    //     $("button[name=next]").click(function (e) {
    //         divs.eq(now).hide();
    //         now = (now + 1 < divs.length) ? now + 1 : 0;
    //         divs.eq(now).show(); // show next
    //     });
    //     $("button[name=prev]").click(function (e) {
    //         divs.eq(now).hide();
    //         now = (now > 0) ? now - 1 : divs.length - 1;
    //         divs.eq(now).show(); // or .css('display','block');
    //         //console.log(divs.length, now);
    //     });
    // });

})(jQuery);
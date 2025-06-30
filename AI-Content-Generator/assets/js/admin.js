jQuery(document).ready(function($) {
    $('#manual_generate').on('click', function(e) {
        e.preventDefault();
        const topic = $('#manual_topic').val();
        const $result = $('#generation_result');
        const $preview = $('#generated_content_preview');
        
        if (!topic) {
            $result.text('Please enter a topic').css('color', 'red');
            return;
        }
        console.log(topic);
        $result.text('Generating...').css('color', 'inherit');
        
        $.post(ajaxurl, {
            action: 'ai_generate_manual_content',
            topic: topic,
            _wpnonce: aiContentGenerator.nonce
        }, function(response) {
            if (response.success) {
                $result.text('Success!').css('color', 'green');
                $('#content_preview').html(response.data.content);
                $preview.show();
                
                // Store the generated data for insertion
                $preview.data('post-data', {
                    title: response.data.title,
                    content: response.data.content,
                    meta_title: response.data.meta_title,
                    meta_description: response.data.meta_description
                });
            } else {
                $result.text(response.data).css('color', 'red');
                $preview.hide();
            }
        }).fail(function() {
            $result.text('Error: Request failed').css('color', 'red');
            $preview.hide();
        });
    });
    
    $('#insert_as_draft').on('click', function() {
        const $preview = $('#generated_content_preview');
        const postData = $preview.data('post-data');
        
        if (!postData) return;
        
        $.post(ajaxurl, {
            action: 'ai_insert_manual_content',
            title: postData.title,
            content: postData.content,
            meta_title: postData.meta_title,
            meta_description: postData.meta_description,
            _wpnonce: aiContentGenerator.nonce
        }, function(response) {
            if (response.success) {
                console.log('Post created as draft! Post ID: ' + response.data.post_id);
                window.location.href = response.data.edit_link;
            } else {
                alert('Error: ' + response.data);
            }
        });
    });
});
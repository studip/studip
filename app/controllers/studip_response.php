<?php
class StudipResponse extends Trails_Response
{
    /**
     * Outputs this response to the client using "echo" and "header".
     *
     * This extension allows the body to be a callable and handles generators
     * by outputting the chunks yielded by the generator.
     */
    public function output()
    {
        if (isset($this->status)) {
            $this->send_header(
                "{$_SERVER['SERVER_PROTOCOL']} {$this->status} {$this->reason}",
                true,
                $this->status
            );
        }

        // Send headers
        foreach ($this->headers as $k => $v) {
            $this->send_header("{$k}: {$v}");
        }

        // Determine output
        if (is_callable($this->body)) {
            $output = call_user_func($this->body);
        } else {
            $output = $this->body;
        }

        if ($output instanceof Generator) {
            // Clear output buffer
            while (ob_get_level()) {
                ob_end_clean();
            }

            // Ensure generator will run to the end
            $abort = ignore_user_abort(true);

            // Output chunks yielded by generator
            foreach ($output as $chunk) {
                if (!connection_aborted()) {
                    echo $chunk;
                    flush();
                }
            }

            // Reset user abort to previous state
            ignore_user_abort($abort);
        } else {
            echo $output;
        }
    }
}

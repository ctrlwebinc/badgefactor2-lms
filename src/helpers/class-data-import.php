<?php
/**
 * Badge Factor 2
 * Copyright (C) 2023 ctrlweb
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @package Badge_Factor_2
 *
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
 */

namespace BadgeFactor2\Helpers;

use BadgeFactor2\BadgrProvider;
use BadgeFactor2\Models\Assertion;
use DateTime;

class DummyProgressBar {

    public function tick() {}

    public function finish() {
        echo " -- " . __('completed!', BF2_DATA['TextDomain']) . "<br />";
    }
}

/**
 * Data import class.
 */
class DataImport {

    private static function output_error( $error_message )
    {
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            WP_CLI::error( $error_message );
        } else {
            if ( defined('DOING_AJAX') && DOING_AJAX ) {
                echo "<br/>" . $error_message . "<br/>";
                echo "<br/>". __( "Cancel file import", BF2_DATA['TextDomain'] ) . "<br/>";
                $output = ob_get_clean();
                wp_send_json( array( 'output' => $output ) );
            } else {
                die( "<br/>" . $error_message . "<br/>");
            }
            
        }
    }

    private static function output_line( $message )
    {
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            WP_CLI::line( $message );
        } else {
            echo $message . "<br/>";
        }
    }

    private static function output_progress( $message, $count )
    {
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            return WP_CLI\Utils\make_progress_bar( $message, $count );
        } else {
            echo $message;
            return new DummyProgressBar();
        }
    }

    public static function batch_process_assertions( string $csv_file, $dry_run = false )
    {
        if ( defined('DOING_AJAX') && DOING_AJAX ) {
            ob_start();
            echo "<h2>". __( 'Import result', BF2_DATA['TextDomain'] ) . "</h2>";
        }
        if ( $dry_run ) {
			static::output_line( __( 'Dry-run mode enabled.', BF2_DATA['TextDomain'] ) );
		}
        $recipients = static::assertions_csv_file_to_recipients_array( $csv_file );
		$badges = static::validate_assertions_recipients_array( $recipients );
		$badges = static::check_for_assertions_duplicate( $badges );
		static::generate_assertions_from_array( $badges, $dry_run );
        if ( defined('DOING_AJAX') && DOING_AJAX ) {
            $output = ob_get_clean();
            wp_send_json( array( 'output' => $output ) );
        }
    }

    public static function assertions_csv_file_to_recipients_array( $file )
    {
        $recipients = array();
		$file_resource = fopen( $file, 'r');
		if ( ! $file_resource ) {
            static::output_error( __( 'Cannot open the csv file! Check your path and filename and try again.', BF2_DATA['TextDomain'] ) );
		}

		static::output_line( __( 'Reading csv file...', BF2_DATA['TextDomain'] ) );

        // Skip first line.
		fgetcsv( $file_resource );

		// Generate an array from the remainder of the csv file.
		while ( ! feof( $file_resource ) ) {
			$recipients[] = fgetcsv($file_resource, 1000, ',');
		}

		fclose( $file_resource );

        return $recipients;
    }

    public static function validate_assertions_recipients_array(array $recipients)
    {
		$today = date('Y-m-d');
		// Generate an array of validated data.
		$progress = static::output_progress( __( 'Validating ', BF2_DATA['TextDomain'] ) . count( $recipients ) . __( ' recipients...', BF2_DATA['TextDomain'] ), count( $recipients ) );
		$badges = array();
		foreach ( $recipients as $key => $recipient ) {
		    $key+=1;
			$badge_class_slug = $recipient[0];

			// Validate each badge class only once.
			if ( ! isset( $badges[$badge_class_slug] ) ) {
				$badge_class = BadgrProvider::get_badge_class_by_badge_class_slug( $badge_class_slug );

				// Exists if badge class does not exist.
				if ( false === $badge_class ) {
                    echo '<br>---<br>';
                    echo __('Line treatment:', BF2_DATA['TextDomain']) . $key;
                    static::output_error( __( 'Badge class does not exist:', BF2_DATA['TextDomain'] ) . $badge_class_slug );
				}
	
				$badges[$badge_class_slug] = array();
			}

			$email = strtolower($recipient[1]);
			if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
                echo '<br>---<br>';
                echo __('Line treatment:', BF2_DATA['TextDomain']) . $key;
                static::output_error( __( 'Invalid email address:', BF2_DATA['TextDomain'] ) . $email );
			}

			$assertion_date = ( isset( $recipient[2] ) && ! empty( $recipient[2] ) ) ? 
				$recipient[2] :
				$today;
            $is_valid_format_date = static::is_valid_format_date($assertion_date);
            if( ! $is_valid_format_date ) {
                echo '<br>---<br>';
                echo __('Line treatment:', BF2_DATA['TextDomain']) . $key;
                static::output_error( __( 'Invalid date format:', BF2_DATA['TextDomain'] ) . $assertion_date );
            }

			// Eliminate duplicate recipient data from csv file.
			if ( ! isset( $badges[$badge_class_slug][$email] ) ) {
				$badges[$badge_class_slug][$email] = $assertion_date;
			}
			$progress->tick();
		}
		$progress->finish();
        return $badges;
    }

    public static function check_for_assertions_duplicate( $badges )
    {
        // Make sure badge is not already given.
		$duplicates = 0;
		$progress = static::output_progress( __( 'Checking for duplicates...', BF2_DATA['TextDomain'] ), array_sum( array_map( 'count', $badges ) ) );
		foreach ( $badges as $badge_class => $emails ) {
			$assertions = Assertion::all( -1, 1, array(
				'filter_type'  => 'Badges',
				'filter_value' => $badge_class,
			) );
			foreach ( $emails as $email => $date ) {
				foreach ( $assertions as $assertion ) {
					if ( $assertion->recipient->plaintextIdentity === $email ) {
						unset($badges[$badge_class][$email]);
						$duplicates++;
					}
				}
				$progress->tick();
			}
		}
		$progress->finish();
		static::output_line( $duplicates . __( 'duplicates removed...', BF2_DATA['TextDomain'] ) );
        return $badges;
    }

    public static function generate_assertions_from_array( $badges, $dry_run = false )
    {
        // Generate assertions in Badgr.
		$progress = static::output_progress( __( 'Generating', BF2_DATA['TextDomain'] ) . array_sum( array_map( 'count', $badges ) ) . __( 'assertions...', BF2_DATA['TextDomain'] ), array_sum( array_map( 'count', $badges ) ) );
		foreach ( $badges as $badge_class => $emails ) {
			foreach ( $emails as $email => $date ) {
				if ( ! $dry_run ) {
					$slug = BadgrProvider::add_assertion( $badge_class, $email, 'email', $date );
				}
				$progress->tick();
			}
		}
		$progress->finish();
        return true;
    }

    public static function is_valid_format_date($date, $format = 'Y-m-d')
    {
        $dt = DateTime::createFromFormat($format, $date);
        return $dt && $dt->format($format) === $date;
    }

}
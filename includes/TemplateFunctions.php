<?php
namespace ShopSpark;

class TemplateFunctions {

	public static function process_html_class( string $class ) {

		return esc_attr( $class );
	}

	/**
	 * * ShopSpark Save Button
	 *
	 * @param string $text The text to display on the button.
	 * @param string $icon The icon to display on the button.
	 * *                     Default is 'save'. 'check', 'cross' are also available.
	 * @param string $class The CSS class for the button.
	 * @return string The HTML for the save button.
	 */
	public static function saveButton( $text = 'Save Changes', $icon = 'save', $class = '' ) {
		$defaultClasses = array(
			'inline-flex',
			'items-center',
			'gap-3',
			'px-6',
			'py-3',
			'bg-gradient-to-r',
			'from-blue-500',
			'to-blue-600',
			'hover:from-blue-600',
			'hover:to-blue-700',
			'text-white',
			'font-semibold',
			'text-sm',
			'rounded-full',
			'shadow-lg',
			'transform',
			'transition-all',
			'duration-200',
			'ease-in-out',
			'hover:scale-105',
			'focus:outline-none',
			'focus:ring-2',
			'focus:ring-blue-500',
			'focus:ring-offset-2',
		);

		if ( $class ) {
			$defaultClasses[] = $class;
		}

		$defaultClasses = implode( ' ', $defaultClasses );
		$defaultClasses = self::process_html_class( $defaultClasses );
		$defaultClasses = is_array( $defaultClasses ) ? implode( ' ', $defaultClasses ) : $defaultClasses;
		$class          = $defaultClasses;

		$icons = array(
			'save'  => 'M5 13l4 4L19 7',
			'check' => 'M5 13l4 4L19 7',
			'cross' => 'M6 18L18 6M6 6l12 12',
		);

		$icon = $icons[ $icon ] ?? $icons['save'];

		return sprintf(
			'<button type="submit" name="submit" id="submit" class="%s">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="%s" />
                </svg>
                %s
            </button>',
			esc_attr( $class ),
			esc_attr( $icon ),
			esc_html( $text )
		);
	}

	/**
	 * * ShopSpark Module Input Field
	 *
	 * @param string $name The name of the input field.
	 * @param string $label The label for the input field.
	 * @param string $type The type of the input field (e.g., 'text', 'checkbox').
	 * @param string $value The value of the input field.
	 * @param string $class The CSS class for the input field.
	 * @param string $placeholder The placeholder text for the input field.
	 * @param string $description The description for the input field.
	 * @param string $id The ID for the input field.
	 * @param string $required Whether the input field is required.
	 */
	public static function moduleInputField(
		$name,
		$label,
		$x_model,
		$value = '',
		$class = '',
		$placeholder = '',
		$description = '',
		$id = '',
		$required = false
	) {
		$default = array(
			'w-full',
			'px-6',
			'!py-[6px]',
			'!pl-[16px]',
			'text-sm',
			'!border-gray-300',
			'!rounded-[12px]',
			'!shadow-md',
			'!focus:outline-none',
			'focus:ring-2',
			'focus:ring-purple-500',
			'focus:ring-offset-white',
			'transition',
			'bg-white',
			'placeholder-gray-400',
			'text-gray-900',
		);

		if ( $class ) {
			$default[] = $class;
		}

		$classes      = implode( ' ', $default );
		$classes      = is_array( $classes ) ? implode( ' ', $classes ) : $classes;
		$classes      = self::process_html_class( $classes );
		$requiredAttr = $required ? 'required' : '';
		$idAttr       = $id ? esc_attr( $id ) : esc_attr( $name );

		return sprintf(
			'<div class="w-full max-w-md mb-6">
                <label for="%1$s" class="block text-sm font-semibold text-gray-800 mb-2">
                    %2$s
                </label>
                <div class="relative">
                    <input
                        type="text"
                        x-model="%8$s"
                        id="%1$s"
                        name="%1$s"
                        value="%3$s"
                        placeholder="%4$s"
                        class="%5$s"
                        %6$s
                    />
                </div>
                %7$s
            </div>',
			esc_attr( $idAttr ),               // %1$s – ID and name
			esc_html( $label ),                // %2$s – Label
			esc_attr( $value ),                // %3$s – Value
			esc_attr( $placeholder ),          // %4$s – Placeholder
			esc_attr( $classes ),              // %5$s – Class list
			$requiredAttr,                   // %6$s – Required attr
			$description ? '<p class="text-xs text-gray-500 mt-1">' . esc_html( $description ) . '</p>' : '', // %7$s – Description
			$x_model ? esc_attr( $x_model ) : '' // %8$s – x-model
		);
	}

	public static function moduleDropdownField( $name, $label, $options = array(), $selected = '', $xModel = '', $id = '', $extraClass = '' ) {
		$id              = $id ?: $name;
		$fieldKey        = esc_attr( $name );
		$fieldLabel      = esc_html( $label );
		$dropdownId      = esc_attr( $id );
		$xModelVar       = $xModel ?: 'selectedOption';
		$defaultSelected = esc_attr( $selected );

		$options = array_map(
			function ( $key, $value ) {
				return "'" . esc_attr( $key ) . "':'" . esc_attr( $value ) . "'";
			},
			array_keys( $options ),
			$options
		);

		$options = '{' . implode( ',', $options ) . '}';

		ob_start();
		?>

		<!-- <div x-data="{ open: false, selected: modalSize, options: ['small', 'medium', 'large'] }" class="relative w-full max-w-md mb-6"> -->
		<div 
		x-data="{ open: false, selected: '<?php echo $defaultSelected; ?>', options:<?php echo $options; ?> }" 
		class="relative w-full max-w-md mb-6 <?php echo esc_attr( $extraClass ); ?>">
		<label for="<?php echo $dropdownId; ?>" class="block text-sm font-semibold text-gray-800 mb-2">
				<?php echo $fieldLabel; ?>
			</label>
			<?php
			$buttonClasses = array(
				'w-full',
				'flex',
				'items-center',
				'justify-between',
				'px-4',
				'py-2.5',
				'bg-white/80',
				'backdrop-blur',
				'border',
				'border-gray-300',
				'rounded-xl',
				'shadow-sm',
				'text-sm',
				'text-gray-800',
				'focus:outline-none',
				'focus:ring-2',
				'focus:ring-purple-500',
				'transition',
			);
			$buttonClasses = implode( ' ', $buttonClasses );
			$buttonClasses = self::process_html_class( $buttonClasses );
			?>
			<button
				type="button"
				@click="open = !open"
				class="<?php echo $buttonClasses; ?>"
			>
				<span x-text="selected.charAt(0).toUpperCase() + selected.slice(1)"></span>
				<svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2"
					viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
				</svg>
			</button>

			<ul
				x-show="open"
				@click.away="open = false"
				@keydown.escape.window="open = false"
				x-transition:enter="transition ease-out duration-100"
				x-transition:enter-start="opacity-0 scale-95"
				x-transition:enter-end="opacity-100 scale-100"
				x-transition:leave="transition ease-in duration-75"
				x-transition:leave-start="opacity-100 scale-100"
				x-transition:leave-end="opacity-0 scale-95"
				class="absolute z-10 mt-2 w-full bg-white border border-gray-200 rounded-xl shadow-lg"
			>
				<template x-for="option in options" :key="option">
					<li @click="selected = option; <?php echo $xModelVar; ?> = option; open = false"
						:class="{'bg-purple-100': selected === option}"
						class="px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 cursor-pointer transition"
						x-text="option.charAt(0).toUpperCase() + option.slice(1)">
					</li>
				</template>
			</ul>

			<input type="hidden" id="<?php echo $dropdownId; ?>" name="<?php echo $fieldKey; ?>" :value="selected" />
		</div>
		<?php
		return ob_get_clean();
	}


	/**
	 * * ShopSpark Include Templates
	 *
	 * @param string $template The template file name (without extension).
	 * @param array  $args Optional. An associative array of arguments to pass to the template.
	 * @return void
	 * @throws \Exception If the template file does not exist.
	 */
	public static function includeTemplate( $template, $args = array() ) {
		$templatePath = SHOP_SPARK_PLUGIN_ADMIN_TEMPLATE_PATH . $template . '.php';

		if ( ! file_exists( $templatePath ) ) {
			echo '<p>' . esc_html__( 'Template not found.', 'shopspark' ) . '</p>';
			return;
		}

		// Merge default args with path
		$args = array_merge(
			$args,
			array(
				'templatePath' => $templatePath,
			)
		);

		// Make variables available in template scope
		extract( $args );

		// Optionally capture output
		ob_start();
		include $templatePath;
		echo ob_get_clean();
	}
}
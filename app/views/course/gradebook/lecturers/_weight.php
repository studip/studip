<label class="gradebook-weight">
    <?= htmlReady($definition->name) ?>
    <div>
        <input type="number" id="definition-<?= $definition->id ?>" name="definitions[<?= $definition->id ?>]" value="<?= htmlReady($definition->weight) ?>" min="0" max="1000000">
        <output name="weight-percent" for="definition-<?= $definition->id ?>"><?= $this->controller->formatAsPercent($this->controller->getNormalizedWeight($definition)) ?></output>
    </div>
</label>
